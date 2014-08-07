<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\Menu;
use bariew\moduleModule\Plugin;
use Yii;
use yii\web\Controller;

/**
 * ItemController implements the CRUD actions for Item model.
 */
class MenuController extends Controller
{
    /**
     * Lists all Item models.
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = new Menu();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Saved');
        }
        return $this->render('update', compact('model'));
    }

    public function actionTest()
    {
        Plugin::test();
    }
}
