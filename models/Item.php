<?php

namespace bariew\moduleModule\models;

use bariew\moduleModule\HtmlOutput;
use Codeception\Platform\SimpleOutput;
use Composer\Console\HtmlOutputFormatter;
use Composer\Factory;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Composer\Console\Application;
use Composer\Command\RequireCommand;
use Symfony\Component\Console\Input\ArrayInput;
use yii\web\HttpException;

class Item extends Model
{
    public $name;
    public $downloads;

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['downloads'], 'integer']
        ];
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
    public static function runComposer(array $command)
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
        $output = new HtmlOutput(fopen('php://stdout', 'w'));
        register_shutdown_function(function () use ($output) {
            foreach ($output->messages as $key => $message) {
               // if (!preg_match())
            }
            //Yii::$app->controller->redirect(['index']);
            print_r($output->messages);
        });
        (new Application())->run(new ArrayInput($command), $output);

        return $output;
    }
}
