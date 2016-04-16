<?php

namespace app\controllers\api\v1;

use Yii;
use yii\web;
use yii\web\Response;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;

use app\models\data\IFileRepository;
use app\models\data\AccessDenied;
use app\models\data\NotFound;
use app\models\File;
use app\models\FileMetadata;

/**
 * Rest api for file
 *
 * @author andrey
 */
class FileController extends Controller {
    
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['options'],
        ];
        return $behaviors;
    }
    
    public function beforeAction($action) {
        $res = parent::beforeAction($action);
        if (!$res) {
            return $res;
        }
        $requestParams = Yii::$app->request->queryParams;
        if (array_key_exists('name', $requestParams)) {
            if (!$this->validateFileName($requestParams['name'])) {
                $response = Yii::$app->response;
                $response->statusCode = 400;
                $response->statusText = 'Incorrect name parameter. Allow only eng/rus literal and number';
                $response->send();
                return FALSE;
            }
        }
        return TRUE;
    }

    private $fileRepository;
    
    public function __construct($id, $module, IFileRepository $fileRepo, $config = array()) {
        
        $this->fileRepository = $fileRepo;
        parent::__construct($id, $module, $config);
    }

    public function actionOptions() {
        $response = Yii::$app->response;
        $response->statusCode = 200;
        
        $response->headers->add('Allow', 'OPTIONS, HEAD, GET, PUT, PATCH, DELETE');
        return;
    }

    /**
     * GET Files list
     * GET with $name return File and metadata in header x-file-metadata
     * HEAD with name send only metadata in header (x-file-metadata) response
     * @param string $name File name
     * @param integer $position position in file
     * @param integer $length length get content
     */
    public function actionIndex($name = NULL, $position = 0, $length = 0)
    {
        $userId = intval(Yii::$app->user->id);
        // get list file
        if (is_null($name) || empty(trim($name))) {
            $metadataList = $this->fileRepository->getFilesMetadata($userId);
            Yii::$app->response->statusCode = 200;
            if (Yii::$app->request->isHead) {
                $this->setMetadataHeader($metadataList);
                return;
            }
            return $metadataList;
        } 
        try {
            $metadata = $this->fileRepository->getFileMetadata($name, $userId);
            
            //check cache
            if ($this->checkIfNoneMatch($metadata->Etag)) {
                Yii::$app->response->statusCode = 304;
                return;
            }

            $this->setMetadataHeader($metadata);
            if (Yii::$app->request->isHead) {
                // send only metadata in header
                Yii::$app->response->statusCode = 200;
                return;
            }
            $fileHandler = $this->fileRepository->getFileStream($name, $userId, $this->isAllowGzipResponse());
            $sizeContent = $metadata->Size;
            Yii::error('!gzip: ' . $this->isAllowGzipResponse());
            if ($this->isAllowGzipResponse()) {
                $this->setGzipCompressionHeader();
                $stat = fstat($fileHandler);
                $sizeContent = $stat['size'];
            }
            $this->setEtag($metadata->Etag);
            Yii::$app->response->sendStreamAsFile($fileHandler, $metadata->Name, 
                ['mimeType' => $metadata->Type, 'fileSize' => $sizeContent]);
        } catch (AccessDenied $ex) {
            Yii::$app->response->statusCode = 403;
        } catch (NotFound $ex) {
            Yii::$app->response->statusCode = 404;
        }
        return;
    }
    
    /**
     * PUT, PATH Upload file, creating file or overwrite existing file
     * PUT with existing file name overwrite this file
     * PATH with $position overwrite part file
     * @param string $name - file name
     * @param integer $position - request of write data to an existing file with $position
     */
    public function actionUpload($name = NULL, $position = 0) {
        Yii::$app->request->enableCsrfValidation = false;
        $userId = intval(Yii::$app->user->id);
        
        if (is_null($name) || empty($name)) {
            \Yii::$app->response->statusCode = 400;
            \Yii::$app->response->statusText = 'Missing \'name\' parameter';
            return;
        }
        $pathToFile = File::getFullPathFile($name);
        if (!file_exists($pathToFile)) {            
            if (\Yii::$app->request->isPatch) {
                \Yii::$app->response->statusCode = 400;
                \Yii::$app->response->statusText = "Atempt to create file with PATCH";
                return;
            }
            //create file
            $dataStream = $this->getFileContentStream($this->haveGzipContent());
            $metadata = $this->fileRepository->createFileFromStream($dataStream, $name, $userId);
            if (is_null($metadata)) {
                Yii::$app->response->statusCode = 500;
                return;
            }
            $this->setMetadataHeader($metadata);
            $this->setEtag($metadata->Etag);
            Yii::$app->response->statusCode = 201;
            return;
        }
        // update file
        $data = $this->getFileContentStream($this->haveGzipContent());
        // if request is put and file exist, overwrite file
        $overwriteFile = Yii::$app->request->isPut;
        try {
            $metadata = $this->fileRepository->updateFileFromStream($data, $name, $userId, $overwriteFile, intval($position));
            $this->setMetadataHeader($metadata);
            $this->setEtag($metadata->Etag);
            Yii::$app->response->statusCode = 200;
        } catch (AccessDenied $ex) {
            Yii::warning('actionUpdate: Access denied. file: ' . $name . ' userId: ' . $userId);
            Yii::$app->response->statusCode = 403;
        } catch (\InvalidArgumentException $ex) {
            Yii::$app->response->statusCode = 400;
            Yii::$app->response->statusText = "Position isn't int or more than the file size";
        }
        return; 
    }
    
    public function actionDelete($name) {
        $userId = intval(Yii::$app->user->id);
        try {
            $this->fileRepository->deleteFile($name, $userId);
            Yii::$app->response->statusCode = 200;
            Yii::$app->response->statusText = "File deleted";
        }
        catch(NotFound $ex) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->statusText = "File not found";
        }
        catch (AccessDenied $ex) {
            Yii::warning('actionDelete: Access denied. file: ' . $name . ' userId: ' . $userId);
            Yii::$app->response->statusCode = 403;
        }
        return;
    }

    /**
     * Sets the header x-file-metadata
     * @param FileMetadata $metadata
     */
    private function setMetadataHeader($metadata) {
        $header = Yii::$app->response->headers;
        $header->add('X-File-Metadata', json_encode($metadata));
    }
    
    /**
     * Checks the file name on invalid symbols
     * Allow: end/rus symbols, 0-9, space, -, _, .
     * @param string $fileName
     * @return boolean
     */
    private function validateFileName($fileName) {
        // ToDo: допускает строку из пробелов, точек, запятых, -, _
        $res = preg_match('/[-_0-9a-zа-я\., ]+/i', $fileName, $mathes);
        if ($res && count($mathes) > 0) {
            return $fileName === array_shift($mathes);
        }
        return FALSE;
    }
    
    /**
     * Check allow gzip respose
     * @return boolean
     */
    private function isAllowGzipResponse() {
        $requestHeaders = \Yii::$app->request->headers;
        if (!$requestHeaders->has('accept-encoding')) {
            return FALSE;
        }
        $acceptEncoding = $requestHeaders->get('accept-encoding');
        return stripos($acceptEncoding, 'gzip') !== FALSE;
    }
    
    /**
     * Set the gzip content-encoding in response header
     */
    private function setGzipCompressionHeader() {
        \Yii::$app->response->headers->set('Content-Encoding', 'gzip');
    }
    
    /**
     * Check is content gzip
     * @return boolean
     */
    private function haveGzipContent() {
        $requestHeaders = \Yii::$app->response->headers;
        if (!$requestHeaders->has('content-encoding')) {
            return FALSE;
        }
        $contentEncoding = $requestHeaders->get('content-encoding');
        return stripos($contentEncoding, 'gzip') !== FALSE;
    }
    
    /**
     * Get a file content strem from request
     * @param boolean $contentIsGzip
     * @return resource
     */
    private function getFileContentStream($contentIsGzip) {
        if ($contentIsGzip) {
            return gzopen("php://input", "rb");
        } else {
            return fopen("php://input", "r");
        }
    }
    
    private function setEtag($hash) {
        $responseHeaders = Yii::$app->response->headers;
        $responseHeaders->set('Etag', $hash);
    }
    
    /**
     * Check cache header (on Etag)
     * @param string $hash
     * @return boolean TRUE if Etag equals
     */
    private function checkIfNoneMatch($hash) {
        $requestHeaders = Yii::$app->request->headers;
        if (!$requestHeaders->has('If-None-Match')) {
            return FALSE;
        }
        $ifNoneMatch = $requestHeaders->get('If-None-Match');
        return $ifNoneMatch === $hash;
    }
}
