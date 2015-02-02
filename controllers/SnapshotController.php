<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\Composer;
use bariew\moduleModule\models\Snapshot;
use Yii;
use yii\web\Controller;

/**
 * SnapshotController implements the CRUD actions for Snapshot model.
 */
class SnapshotController extends Controller
{

    public function actionIndex()
    {
        return (new Snapshot())->compact();
    }
}
