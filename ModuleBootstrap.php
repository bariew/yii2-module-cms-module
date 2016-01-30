<?php
/**
 * ModuleBootstrap class file
 * @copyright Copyright (c) 2014 Bariew
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\moduleModule;

use bariew\moduleModule\controllers\ItemController;
use Yii;
use yii\base\BootstrapInterface;
use \yii\web\Application;
use yii\web\View;

/**
 * Bootstrap class initiates external modules.
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ModuleBootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if (!$app->hasModule('module')) {
            return true;
        }
        $menuEnabled = $app instanceof Application
            && $app->getModule('module')->params['enableMenu'];
        if ($menuEnabled) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderMenu']);
            });
        }
        return true;
    }

    public function renderMenu()
    {
        if (Yii::$app->getRequest()->getIsAjax()) {
            return true;
        }
        if ((!$module = Yii::$app->getModule('module')) || $module->params['enableMenu'] == false){
            return true;
        }
        try {
            $controller = new ItemController('item', \Yii::$app->getModule('module'));
            return $controller->runAction('menu');
        } catch (\Exception $e) {}
    }
}