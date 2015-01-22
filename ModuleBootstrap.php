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
use yii\helpers\FileHelper;
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
        if (!$module = Item::getModuleByClassName(Module::className())) {
            return true;
        }
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

    public static function postCreateProject($event)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        if (isset($params[__METHOD__]) && is_array($params[__METHOD__])) {
            foreach ($params[__METHOD__] as $method => $args) {
                call_user_func_array([__CLASS__, $method], (array) $args);
            }
        }
    }

    /**
     * Sets the correct permission for the files and directories listed in the extra section.
     * @param array $paths the paths (keys) and the corresponding permission octal strings (values)
     */
    public static function setPermission(array $paths)
    {
        foreach ($paths as $path => $permission) {
            self::chmod_r($path, $permission);
        }
    }

    /**
     * Changing permissions recursively
     * @param $path
     * @param $permission
     * @author Zdenda Zener
     * @link http://stackoverflow.com/questions/9262622/set-permissions-for-all-files-and-folders-recursively
     */
    private static function chmod_r($path, $permission) {
        if (is_file($path)) {
            return chmod($path, octdec($permission));
        } else if (!is_dir($path)) {
            return false;
        }
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $item) {
            chmod($item->getPathname(), octdec($permission));
            if ($item->isDir() && !$item->isDot()) {
                self::chmod_r($item->getPathname(), $permission);
            }
        }
    }
}