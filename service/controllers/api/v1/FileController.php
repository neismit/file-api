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

//    public function behaviors()
//    {
//        return [
//            'verbs' => [
//                'class' => \yii\filters\VerbFilter::className(),
//                'actions' => [
//                    'index'  => ['get', 'head'],
//                ],
//            ],
//        ];
//    }
    
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
            return;
        } else {
            $response->headers->add('Allow', 'OPTIONS, HEAD, GET, PUT, PATCH, DELETE');
        }
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
                    $header = Yii::$app->response->headers;
                    $header->add('x-file-metadata', json_encode($metadata));
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
        $metadata = FileMetadata::createMetadata($name, 'text/plain', '1');
        $this->fileRepository->saveFileMetadata($metadata);
        return 200;
    }
}
