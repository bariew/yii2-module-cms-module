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

    protected $app;
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

}