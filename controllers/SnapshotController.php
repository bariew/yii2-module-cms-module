<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\Snapshot;
use Yii;
use yii\web\Controller;

/**
 * SnapshotController implements the CRUD actions for Snapshot model.
 */
class SnapshotController extends Controller
{
    public function actionCreate()
    {
        $model = new Snapshot();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $model->compact() && $this->goBack();
        }
        return $this->render('create', compact('model'));
    }
}
