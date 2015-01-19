<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;


use bariew\moduleModule\HtmlOutput;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Yii;

class Composer extends Model
{
    public $name;
    public $description;
    public $downloads;
    public $url;
    public $favers;
    public $repository;
    public $alias;
    public $version;
    public $bootstrap;

    public function rules()
    {
        return [
            ['name', 'string']
        ];
    }

    public function search($params)
    {
        $query = "https://packagist.org/search.json?tags[]=yii2&tags[]=module";
        if (isset($params['page'])) {
            $query .= '&page='.$params['page'];
        }
        $this->load($params);
        if ($this->name) {
            $query .= "&q=".$this->name;
        }
        $response = json_decode(file_get_contents($query), true);
        $items = $response["results"];
        foreach ($items as $key => $attributes) {
            $items[$key] = new self(compact('attributes'));
        }
        $dataProvider = new ArrayDataProvider(['allModels' => $items, 'key' => function ($model) {return $model['name']; }]);
        $dataProvider->setModels($items);
        $dataProvider->pagination->pageSize = 15;
        $dataProvider->pagination->totalCount = $response['total'];
        return $dataProvider;
    }


    public static function install($names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $key => &$name) {
            $name .= ":dev-master";
        }
        return self::runComposer([
            'command' => 'require',
            'packages' => $names,
            '--no-update' => true,
        ]);
    }

    public static function update($names)
    {
        if (!$names) {
            return true;
        }
        return self::runComposer([
            'command' => 'update',
            'packages' => $names
        ]);
    }

    public static function remove($names)
    {
        if (!$names) {
            return true;
        }
        return self::runComposer([
            'command' => 'remove',
            'packages' => $names
        ]);
    }

    public static function runComposer(array $command, $render = true)
    {
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
            foreach ($output->messages as $key => $message) {
                if (preg_match('/Exception/', $message)) {
                    Yii::$app->session->setFlash('error', $output->messages[$key+1]);
                    break;
                } elseif (preg_match('/Problem.*/', $message)) {
                    Yii::$app->session->setFlash('error', $message);
                    break;
                }
                Yii::$app->session->setFlash('info', $message);
            }
            echo Yii::$app->controller->actionIndex();
        });
        return $output;
    }
} 