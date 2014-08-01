<?php

namespace bariew\moduleModule\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;


class Item extends Model
{

    public static function search()
    {
//        $client = new \Packagist\Api\Client();
//        $items = $client->search('yii2-cms-module');
        $items = json_decode(file_get_contents("https://packagist.org/search.json?q=yii2-cms-module"), true)["results"];

        return new ArrayDataProvider(['allModels' => $items]);
    }
}
