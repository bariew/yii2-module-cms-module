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

    public function actionPaths($query)
    {
        $s = DIRECTORY_SEPARATOR;
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (preg_match('/.+'.preg_quote($s, '/').'$/', $query)) {
            $query .= 'file'; // added random string for getting dir content of query with '/' in the end.
        }
        $path = Yii::getAlias('@app/'. dirname($query))  . $s;
        $result = array_diff(scandir($path), ['.', '..']);
        foreach ($result as $key => $dir) {
            $result[$key] = dirname($query) . $s . $dir;
            if (!is_dir($path . $s . $dir)) {
                unset ($result[$key]);
            }
        }

        return array_values($result);
    }
}
