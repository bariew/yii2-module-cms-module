<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\CloneModel;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * ItemController implements the CRUD actions for Item model.
 */
class CloneController extends Controller
{
    public function actionCreate()
    {
        $model = new CloneModel();
        if ($model->load(Yii::$app->request->post()) && $model->run()) {
            Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Module successfully cloned'));
        }
        return $this->render('create', compact('model'));
    }

    public function actionAliases($query)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return CloneModel::findAliases($query);
    }
}
