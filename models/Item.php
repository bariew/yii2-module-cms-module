<?php

namespace bariew\moduleModule\models;

use bariew\moduleModule\HtmlOutput;
use bariew\moduleModule\ModuleBootstrap;
use Codeception\Platform\SimpleOutput;
use SebastianBergmann\Exporter\Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Composer\Console\Application;
use yii\console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use yii\console\controllers\MigrateController;
use yii\helpers\BaseFileHelper;

class Item extends Model
{
    public $name;
    public $description;
    public $downloads;
    public $url;
    public $favers;
    public $repository;

    public static $extConfigFile = '@vendor/yiisoft/extensions.php';

    public static $migrationCommands = [];

    public function rules()
    {
        return [
            [['name', 'url', 'repository', 'description'], 'string'],
            [['downloads', 'favers'], 'integer'],
            [['name'], 'moduleNameValidation']
        ];
    }

    public function moduleNameValidation($attribute)
    {
        if (!self::getModuleName($this->$attribute)) {
            $this->addError($attribute, "Not CMS module");
        }
    }

    public static function composerConfig()
    {
        return json_decode(file_get_contents(
            Yii::$app->basePath . DIRECTORY_SEPARATOR . 'composer.json'
        ), true);
    }

    public static function installedList()
    {
        return Yii::$app->extensions;
    }

    public function getIsInstalled()
    {
        return isset(self::installedList()[$this->name]);
    }

    public static function getModuleName($name)
    {
        return  (preg_match('/yii2-(.*)-cms-module/', $name, $matches))
            ? $matches[1] : null;
    }

    public static function search()
    {
        $items = json_decode(file_get_contents("https://packagist.org/search.json?q=yii2-cms-module"), true)["results"];
        foreach ($items as $key => $attributes) {
            $items[$key] = new self(compact('attributes'));
            if (!$items[$key]->validate()) {
                unset($items[$key]);
            }
        }
        return new ArrayDataProvider(['allModels' => $items, 'key' => function ($model) {return $model['name']; }]);
    }


    public static function install(array $names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $name) {
            if (!$module = self::getModuleName($name)) {
                continue;
            }
            self::$migrationCommands[] = ['module-up', [$module]];
        }
        return self::runComposer([
            'command' => 'require',
            'packages' => $names
        ]);
    }

    public static function remove(array $names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $name) {
            if (!$moduleName = self::getModuleName($name)) {
                continue;
            }
            if(!$module = Yii::$app->getModule($moduleName)) {
                continue;
            }
            if (method_exists($module, 'uninstall')) {
                call_user_func([$module, 'uninstall']);
            }
            self::$migrationCommands[] = ['module-down', [$moduleName]];
        }
        self::migrate(self::$migrationCommands);
        return self::runComposer([
            'command' => 'remove',
            'packages' => $names
        ]);
    }


    protected static function checkRequirements()
    {
        $s = DIRECTORY_SEPARATOR;
        $root = Yii::$app->basePath;
        $composerSettings = self::composerConfig();
        foreach ($composerSettings['extra']['writable'] as $path) {
            $fullPath = $root . $s . $path;
            $fullPath = is_file($fullPath) ? $fullPath : $fullPath.$s;
            if (!is_writable($fullPath)) {
                throw new InvalidConfigException("{$path} is not writable");
            }
        }
    }

    public static function runComposer(array $command, $render = true)
    {
        self::checkRequirements();
        chdir(Yii::$app->basePath);
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        foreach (['xdebug_stop_code_coverage', 'xdebug_disable'] as $function) {
            if (function_exists($function)) {
                $function();
            }
        }
        $output = $render ? self::htmlOutput() : null;

        return (new Application())->run(new ArrayInput($command), $output);
    }

    public static function htmlOutput()
    {
        $output = new HtmlOutput(fopen('php://stdout', 'w'));
        register_shutdown_function(function () use ($output) {
            $success = true;
            foreach ($output->messages as $key => $message) {
                Yii::$app->session->setFlash('info', $message);
                if (preg_match('/Exception/', $message)) {
                    $success = false;
                    Yii::$app->session->setFlash('error', $output->messages[$key+1]);
                    break;
                }
            }
            Yii::$app->extensions = require Yii::getAlias(self::$extConfigFile);
            $bootstrap = new ModuleBootstrap();
            $bootstrap->app = Yii::$app;
            $bootstrap->attachModules();
            if ($success) {
                self::migrate(self::$migrationCommands);
                Yii::$app->session->setFlash('success', "Ready.");
            }
            echo Yii::$app->controller->actionIndex();
        });
        return $output;
    }

    protected static function migrate(&$actions)
    {
        if (!$actions) {
            return true;
        }
        $webApp = Yii::$app;
        try {
            $consoleConfig = require_once Yii::getAlias('@app/config/console.php');
            Yii::$app = new ConsoleApplication($consoleConfig);
            /**
             * @var MigrateController $controller
             */
            $controller =  Yii::$app->createController('migrate')[0];
            $controller->interactive = false;
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            defined('YII_DEBUG') or define('YII_DEBUG', true);
            defined('YII_ENV') or define('YII_ENV', 'dev');
            defined('STDOUT') or define ('STDOUT', 'php://stdout');
            foreach ($actions as $action) {
                $controller->runAction($action[0], $action[1]);
            }
            Yii::$app->response->clearOutputBuffers();
        } catch (\Exception $e) {
            Yii::$app = $webApp;
            echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return false;
        }
        $actions = [];
        Yii::$app = $webApp;
        return true;
    }
}
