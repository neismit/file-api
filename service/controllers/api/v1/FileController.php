<?php

namespace app\controllers\api\v1;

use Yii;
use yii\rest\Controller;
use app\models\data\IFileRepository;
use app\models\data\AccessDenied;
use yii\web;
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
                    Yii::error('FullPath: ' . $fullPath);
                    $fileHandler = File::getFileStream($fullPath);
                    Yii::$app->response->sendStreamAsFile($fileHandler);
                }
            } catch (AccessDenied $ex) {
                Yii::$app->response->statusCode = 403;
                return;
            }
        }
    }
    
    public function actionView($name)
    {
        $folder = Yii::$app->params->get('dataFolder');
        return;
    }
}
