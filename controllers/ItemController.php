<?php

namespace bariew\moduleModule\controllers;

use bariew\configModule\models\Params;
use bariew\moduleModule\models\Item;
use bariew\moduleModule\models\Param;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

use bariew\moduleModule\widgets\MenuWidget;
use yii\bootstrap\NavBar;
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
        $items = Item::updateAll(Yii::$app->request->post('Item'));
        if ($items === true) {
            Yii::$app->session->setFlash('info', Yii::t('modules/module', 'Updated'));
            return $this->refresh();
        } else if (!$items) {
            $items = Item::findAll();
        }
        return $this->render('index', [
            'dataProvider' =>  new ArrayDataProvider([
                'allModels' => $items, 'key' => 'id'
            ]),
        ]);
    }

    public function actionMigrate()
    {
        $actions = [['up', ['all']]];
        Item::migrate($actions);
        Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Successful migration!'));
        return $this->redirect('index');
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

    public function actionMenu()
    {
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo MenuWidget::widget([
            'options' => ['class' => 'navbar-nav navbar-right']
        ]);

        NavBar::end();
    }

}
