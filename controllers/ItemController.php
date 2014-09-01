<?php

namespace bariew\moduleModule\controllers;

use bariew\configModule\models\Params;
use bariew\moduleModule\models\Item;
use bariew\moduleModule\models\Param;
use Yii;
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
        return $this->render('index', [
            'dataProvider' => Item::findAll(),
        ]);
    }

    public function actionSearch()
    {
        $searchModel = new Item();
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        $render = Yii::$app->request->isAjax ? 'renderPartial' : 'render';
        return $this->$render('search', compact('searchModel', 'dataProvider'));
    }

    public function actionInstall()
    {
        $items = Yii::$app->request->post('isInstalled') ? : [];
        $toInstall = [];
        $toRemove = [];
        $installedItems = Item::installedList();
        foreach ($items as $name => $value) {
            $installed = isset($installedItems[$name]);
            if ($value && !$installed) {
                $toInstall[] = $name . ":dev-master";
            } else if (!$value && $installed){
                $toRemove[] = $name;
            }
        }
        Item::remove($toRemove);
        Item::install($toInstall);
        Yii::$app->session->setFlash('info', Yii::t('modules/module', 'Nothing to install/remove'));
        return $this->runAction('index');
    }

    public function actionError($message)
    {
        echo $message;
        return $this->render('error', compact('message'));
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
        $model = new Param(['name' => Item::getModuleName($id)]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Saved'));
        }
        return $this->render('params', compact('model'));
    }

}
