<?php

namespace bariew\moduleModule\controllers;

use bariew\configModule\models\Params;
use bariew\moduleModule\models\Item;
use bariew\moduleModule\models\Param;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

/**
 * ItemController implements the CRUD actions for Item model.
 */
class ItemController extends Controller
{
    /**
     * Lists all Item models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Item::updateAll(Yii::$app->request->post('Item'))) {
            Yii::$app->session->setFlash('info', Yii::t('modules/module', 'Updated'));
            $this->refresh();
        }
        return $this->render('index', [
            'dataProvider' =>  new ArrayDataProvider([
                'allModels' => Item::findAll(), 'key' => 'id'
            ]),
        ]);
    }

    public function actionMigrate()
    {
        $actions = [['up', ['all']]];
        Item::migrate($actions);
        Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Successful migration!'));
        return $this->runAction('index');

    }

    public function actionParams($id)
    {
        $model = new Param(['item' => Item::findOne($id)]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Saved'));
            $this->refresh();
        }
        return $this->render('params', compact('model'));
    }

}
