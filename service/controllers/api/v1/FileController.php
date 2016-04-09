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
            $response->headers->add('Allow', 'OPTIONS, GET, PUT, PATCH, DELETE');
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
            return $this->fileRepository->getFiles($userId);
        } else {            
            // не самое лучшее решение для быстродействия, но зато красивее
            try {
                $metadata = $this->fileRepository->getFileMetadata($name, $userId);
                if(is_null($metadata)) {
                    Yii::$app->response->statusCode = 404;
                    return;
                } else {
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
                }
            } catch (AccessDenied $ex) {
                Yii::$app->response->statusCode = 403;
                return;
            }
        }
    }
    
    public function actionView($name)
    {
        //$folder = Yii::$app->params->get('dataFolder');
        $metadata = FileMetadata::createMetadata($name, '1');
        $this->fileRepository->saveFileMetadata($metadata);
        return 200;
    }
    
//    public function actionCreate($overwrite = FALSE) 
//    {
//        Yii::$app->request->enableCsrfValidation = false;
//        $request = Yii::$app->request;
//        Yii::error('Request headers: ' . print_r($request->headers));
//        $userId = 1;
//        
//        $pathToFile = File::getFullPathFile($name);
//        if (file_exists($pathToFile) && $overwrite) {
//            $metadata = $this->fileRepository->getFileMetadata($name, $userId);
//            if (is_null($metadata)) {
//                Yii::error('actionCreate: $metadata is null for file: ' . $pathToFile);
//                Yii::$app->response->statusCode = 500;
//                return;
//            }
//            // rewrite file
//            if ($metadata->Owner === $userId) {
//                
//                
// 
//                
//                throw new \Exception();
//            } else {
//                Yii::warning('actionCreate: Access denied. file: ' . $pathToFile . ' userId: ' . $userId);
//                Yii::$app->response->statusCode = 403;
//                return;
//            }
//        }
//        
//        $data = fopen("php://input", "r");
//        if ($this->fileRepository->createFileFromStream($data, '', $userId)) {
//            Yii::$app->response->statusCode = 201;
//            $metadata = $this->fileRepository->getFileMetadata($name, $userId);
//            $header = Yii::$app->response->headers;
//            $header->add('x-file-metadata', json_encode($metadata));
//            return;
//        }
//        
//        
//        return;
//    }
    
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
        if (file_exists($pathToFile) && $overwrite) {
            $metadata = $this->fileRepository->getFileMetadata($name, $userId);
            if (is_null($metadata)) {
                Yii::error('actionUpload: $metadata is null for file: ' . $pathToFile);
                // clear file
                $this->fileRepository->deleteFile(); // not implement
                Yii::$app->response->statusCode = 500;
                return;
            }
            // rewrite file
            if ($metadata->Owner === $userId) {
                $data = fopen("php://input", "r");
                
                throw  new Exception();
                $result = $this->fileRepository->updateFileFromStream($data, $name, $position, $userId);
                $metadata->update();                
                $this->fileRepository->saveFileMetadata($metadata);
                $this->setMetadataHeader($metadata);
                Yii::$app->response->statusCode = 200;
                return;
            } else {
                Yii::warning('actionCreate: Access denied. file: ' . $pathToFile . ' userId: ' . $userId);
                Yii::$app->response->statusCode = 403;
                return;
            }
        } else {
            //create file
            $data = fopen("php://input", "r");
            $this->fileRepository->createFileFromStream($data, $name, $userId);
            $metadata = FileMetadata::createMetadata($name, $userId);
            $this->setMetadataHeader($metadata);
            Yii::$app->response->statusCode = 201;
            return;
        }
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
