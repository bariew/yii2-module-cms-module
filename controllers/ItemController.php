<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\Item;
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
        $searchModel = new Item();
        $dataProvider = $searchModel->search();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionInstall()
    {
        $items = Yii::$app->request->post('isInstalled');
        $toInstall = [];
        $toRemove = [];
        $installedItems = Item::installedList();
        foreach ($items as $name => $value) {
            $installed = isset($installedItems[$name]);
            if ($value && !$installed) {
                $toInstall[] = $name . ":";
            } else if (!$value && $installed){
                $toRemove[] = $name;
            }
        }
        Item::remove($toRemove);
        Item::install($toInstall);
    }

    public function actionError($message)
    {
        echo $message;
        return $this->render('error', compact('message'));
    }

}
