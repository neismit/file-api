<?php

namespace app\controllers\api\v1;

use Yii;
use yii\web;
use yii\web\Response;
use yii\rest\Controller;
use app\models\data\IFileRepository;
use app\models\data\AccessDenied;
use app\models\File;
use app\models\FileMetadata;

/**
 * Rest api for file
 *
 * @author andrey
 */
class FileController extends Controller {

    private $fileRepository;
    
    public function __construct($id, $module, IFileRepository $fileRepo, $config = array()) {
        
        $this->fileRepository = $fileRepo;
        parent::__construct($id, $module, $config);
    }

    public function actionOptions($name = NULL) {
        $response = Yii::$app->response;
        $response->statusCode = 200;
        if (is_null($name)) {
            $response->headers->add('Allow', 'OPTIONS, GET, HEAD');
        } else {
            $response->headers->add('Allow', 'OPTIONS, HEAD, GET, PUT, PATCH, DELETE');
        }
        return;
    }

        /**
     * GET Files list
     * GET with $name return File and metadata in header x-file-metadata
     * HEAD with name send only metadata in header (x-file-metadata) response
     * @param string $name File name
     */
    public function actionIndex($name = NULL)
    {
        $userId = 1;
        if (is_null($name)) {
            $metadataList = $this->fileRepository->getFilesMetadata($userId);
            Yii::$app->response->statusCode = 200;
            if (Yii::$app->request->isHead) {
                $this->setMetadataHeader($metadataList);
                return;
            }
            return $metadataList;
        } else {            
            // не самое лучшее решение для быстродействия, но зато красивее
            try {
                $metadata = $this->fileRepository->getFileMetadata($name, $userId);
                if(is_null($metadata)) {
                    Yii::$app->response->statusCode = 404;
                    return;
                }
                $fullPath = File::getFullPathFile($metadata->Name);
                $fileHandler = File::getFileStream($fullPath);
                if (is_null($fileHandler)) {
                    Yii::error($fullPath . ' - file not exist, metadata loaded');
                    Yii::$app->response->statusCode = 500;
                    return;
                }
                $this->setMetadataHeader($metadata);
                if (Yii::$app->request->isHead) {
                    // send only metadata in header
                    Yii::$app->response->statusCode = 200;
                    return;
                }

                Yii::$app->response->sendStreamAsFile($fileHandler, $metadata->Name, 
                    ['mimeType' => $metadata->Type, 'fileSize' => $metadata->Size]);
            } catch (AccessDenied $ex) {
                Yii::$app->response->statusCode = 403;
            }
            return;
        }
    }
    
    /**
     * PUT, PATH Upload file, creating file or overwrite existing file
     * @param string $name - file name
     * @param boolean $overwrite - overwrite exist file
     * @param integer $position - request of write data to an existing file with $position
     */
    public function actionUpload($name = NULL, $overwrite = FALSE, $position = 0) {
        Yii::$app->request->enableCsrfValidation = false;
        $userId = 1;
        
        if (is_null($name)) {
            Yii::$app->response->statusCode = 400;
            return "Missing 'name' parameter in request";
        }
        
        $pathToFile = File::getFullPathFile($name);
        if (file_exists($pathToFile)) {
            $metadata = $this->fileRepository->getFileMetadata($name, $userId);
            if (is_null($metadata)) {
                Yii::error('actionUpload: $metadata is null for file: ' . $pathToFile);
                // clear file
                $this->fileRepository->deleteFile(); // not implement
                Yii::$app->response->statusCode = 500;
                return;
            }
            if ($metadata->Owner !== $userId) {
                Yii::warning('actionCreate: Access denied. file: ' . $pathToFile . ' userId: ' . $userId);
                Yii::$app->response->statusCode = 403;
                return;
            }
            if (!$overwrite) {
                Yii::$app->response->statusCode = 400;
                Yii::$app->response->content = "Atempt to change existing file, but 'overwrite = false";
                return;
            }
            
            // rewrite file
            $data = fopen("php://input", "r");
            if (is_null($data)) {
                Yii::$app->response->statusCode = 204;
                Yii::$app->response->content = "Atach a file to the request";
                return;
            }
            $resUpdate = $this->fileRepository->updateFileFromStream($data, $name, $position);
            if (!$resUpdate) {
                Yii::error('actionUpload: file not update');
                Yii::$app->response->statusCode = 500;
                return;
            }
            clearstatcache();
            $metadata->update();                
            $this->fileRepository->saveFileMetadata($metadata);
            $this->setMetadataHeader($metadata);
            Yii::$app->response->statusCode = 200;
            return;
        } else {
            if (\Yii::$app->request->isPatch) {
                Yii::$app->response->statusCode = 400;
                Yii::$app->response->content = "Atempt to create file with PATCH";
            }         
            //create file
            $data = fopen("php://input", "r");
            $this->fileRepository->createFileFromStream($data, $name);
            $metadata = FileMetadata::createMetadata($name, $userId);
            $this->fileRepository->saveFileMetadata($metadata);
            $this->setMetadataHeader($metadata);
            Yii::$app->response->statusCode = 201;
            return;
        }
    }
    
    public function actionDelete($name) {
        $userId = 1;
        try {
            $this->fileRepository->deleteFile($name, $userId);
            Yii::$app->response->statusCode = 200;
            Yii::$app->response->content = "File deleted";
        }
        catch(\InvalidArgumentException $ex) {
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->content = "File not found";
        }
        catch (AccessDenied $ex) {
            Yii::warning('actionCreate: Access denied. file: ' . $name . ' userId: ' . $userId);
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
        $header->add('x-file-metadata', json_encode($metadata));
    }
}
