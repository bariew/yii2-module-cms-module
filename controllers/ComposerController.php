<?php

namespace bariew\moduleModule\controllers;

use bariew\moduleModule\models\Composer;
use Yii;
use yii\web\Controller;

/**
 * ItemController implements the CRUD actions for Item model.
 */
class ComposerController extends Controller
{
    /**
     * Lists all Item models.
     * @param bool $runComposer whether to run composer install/uninstall
     * @return mixed
     */
    public function actionIndex($runComposer = true)
    {
        $model = new Composer();
        if ($runComposer && $toInstall = Yii::$app->request->post('install')) {
            $actions = [];
            foreach ($toInstall as $name => $value) {
                $actions[$value][] = $name;
            }
            $model::removeAll(@$actions[Composer::ACTION_REMOVE]);
            $model::requireAll(@$actions[Composer::ACTION_INSTALL]);
            $model::updateAll(@$actions[Composer::ACTION_UPDATE]);
        }

        return $this->render('index', compact('model'));
    }
}
