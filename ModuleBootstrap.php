<?php
/**
 * ModuleBootstrap class file
 * @copyright Copyright (c) 2014 Bariew
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\moduleModule;

use bariew\moduleModule\controllers\ItemController;
use bariew\moduleModule\models\Item;
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
        $app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
        if ($app instanceof Application) {
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
        $module = Item::getModuleByClassName(Module::className());
        try {
            $controller = new ItemController('item', $module);
            return $controller->runAction('menu');
        } catch (\Exception $e) {}
    }
}