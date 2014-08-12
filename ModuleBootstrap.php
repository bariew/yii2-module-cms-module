<?php
/**
 * ModuleBootstrap class file
 * @copyright Copyright (c) 2014 Galament
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\moduleModule;

use bariew\moduleModule\models\Menu;
use bariew\moduleModule\widgets\MenuWidget;
use Yii;
use yii\base\BootstrapInterface;
use yii\composer\Installer;
use \yii\web\Application;
use yii\base\Event;
use yii\bootstrap\NavBar;
use yii\web\View;

/**
 * Bootstrap class initiates external modules.
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ModuleBootstrap implements BootstrapInterface
{
    protected static $paths = [
        "vendor",
        "composer.json",
        "composer.lock"
    ];

    public $app;
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderMenu']);
            });
        }

        $this->app = $app;
        $this->attachModules()
            ->attachMigrations();

        return true;
    }

    /**
     * attaches modules to application from external
     * composer installed extensions sources
     */
    public function attachModules()
    {
        $modules = $this->app->modules;
        foreach ($this->app->extensions as $name => $config) {
            $extName = preg_replace('/.*\/(.*)$/', '$1', $name);
            if(!preg_match('/yii2-(.+)-cms-module/', $extName, $matches)){
                continue;
            }
            $moduleName = $matches[1];
            $alias = key($config['alias']);
            $basePath = $config['alias'][$alias];
            $paramPath = $basePath . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR . 'main.php';
            $params =  file_exists($paramPath) ? require $paramPath : [];
            $params['moduleAlias'] = $alias;
            $modules[$moduleName] = [
                'class'     => str_replace(['@', '/'], ['\\', '\\'], $alias) .'\Module',
                'basePath'  => $basePath,
                'params'    => $params,
            ];
        }
        \Yii::configure($this->app, compact('modules'));
        return $this;
    }

    /**
     * Attaches advanced module migration controller
     * for migrating from modules root /migrations folder
     * @return ModuleBootstrap this
     */
    public function attachMigrations()
    {
        $this->app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
        return $this;
    }

    public function renderMenu(Event $event)
    {
        if (Yii::$app->getRequest()->getIsAjax() || !Menu::isActive()) {
            return;
        }

        NavBar::begin([
            'brandLabel' => 'Home',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        if (\Yii::$app->has('i18n') && isset(\Yii::$app->i18n->widget)) {
            echo "<div class='btn pull-right'>".Yii::$app->i18n->widget."</div>";
        }
        echo MenuWidget::widget([
            'options' => ['class' => 'navbar-nav navbar-right']
        ]);

        NavBar::end();
    }


    const EXTRA_WRITABLE = 'writable';
    const EXTRA_EXECUTABLE = 'executable';
    /**
     * Sets the correct permission for the files and directories listed in the extra section.
     * @param CommandEvent $event
     */
    public static function setPermission($event)
    {
        foreach (self::$paths as $path) {
            echo "Setting writable: $path ...\n";
            self::createPath($path);
            self::chmodR($path);
        }
    }

    public static function test()
    {
        foreach (self::$paths as $path) {
            $path = Yii::$app->basePath . DIRECTORY_SEPARATOR . $path;
            self::createPath($path);
            self::chmodR($path);
        }
        exit;
    }

    public static function createPath($path)
    {
        if (file_exists($path)) {
            return;
        }
        if (preg_match('/.*\.\w+$/', $path)) {
            touch($path);
        } else {
            mkdir($path);
        }
    }

    public static function chmodR($path) {
        chmod($path, 0777);
        if (is_file($path)) {
            return;
        }
        foreach (scandir($path) as $file) {
            if(in_array($file, ['.', '..'])) {
                continue;
            }
            $subpath = $path . DIRECTORY_SEPARATOR . $file;
            self::chmodR($subpath);
        }
    }
}