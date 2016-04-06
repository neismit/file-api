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

    /**
     * GET Files list
     * GET with name File and metadata
     * HEAD with name send only metadata in header response
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
                    
                    $header = Yii::$app->response->headers;
                    $header->add('x-file-metadata', json_encode($metadata));
                    Yii::trace('name: ' . $name);
                    if (Yii::$app->request->isHead) {
                        Yii::trace(__METHOD__);
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
