<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleMigration\ModuleMigrateController;
use bariew\moduleModule\Module;
use bariew\moduleModule\models\Snapshot;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * SnapshotController implements the CRUD actions for Snapshot model.
 */
class SnapshotController extends Controller
{
    public function actionCreate()
    {
        return (new Snapshot())->compact();
    }
}
