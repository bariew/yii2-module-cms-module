<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/25/14
 * Time: 6:20 PM
 */

namespace bariew\moduleModule\models;


use bariew\moduleModule\components\FileModel;
use Yii;

class Param extends FileModel
{
    public $name;

    public function getPath()
    {
        return Yii::$app->getModule($this->name)->basePath . DIRECTORY_SEPARATOR . 'params-local.php';
    }

    public function init()
    {
        $path = Yii::$app->getModule($this->name)->basePath . DIRECTORY_SEPARATOR . 'params.php';
        $this->setFileAttributes($path);
        parent::init();
    }

} 