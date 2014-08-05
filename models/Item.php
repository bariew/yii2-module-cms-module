<?php

namespace bariew\moduleModule\models;

use bariew\moduleModule\HtmlOutput;
use Codeception\Platform\SimpleOutput;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class Item extends Model
{
    public $name;
    public $downloads;
    public $url;
    public $favers;
    public $repository;

    public function rules()
    {
        return [
            [['name', 'url', 'repository'], 'string'],
            [['downloads', 'favers'], 'integer'],
            [['name'], 'moduleNameValidation']
        ];
    }

    public function moduleNameValidation($attribute)
    {
        if (!preg_match('/yii2-.*-cms-module/', $this->$attribute)) {
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
                if (preg_match('/Exception/', $message)) {
                    $success = false;
                    Yii::$app->session->setFlash('error', $output->messages[$key+1]);
                    break;
                }
            }
            if ($success) {
                Yii::$app->session->setFlash('success', "Successfully installed.");
            }
            echo Yii::$app->controller->actionIndex();
        });
        return $output;
    }
}
