<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\Module;
use yii\console\controllers\MigrateController;
use bariew\moduleModule\models\Snapshot;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * SnapshotController implements the CRUD actions for Snapshot model.
 */
class SnapshotController extends Controller
{

    public function actionIndex()
    {
        $controller = new MigrateController('migrate', new Module('module'));
        $controller->runAction('up');
    }
    public function actionCreate()
    {
        return (new Snapshot())->compact();
    }

    public function actionUpload()
    {
        if ($file = UploadedFile::getInstanceByName('upload')) {
            (new Snapshot())->extract($file);
        }
        return $this->render('upload');
    }
}
