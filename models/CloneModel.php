<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use bariew\moduleMigration\ModuleMigration;
use bariew\moduleModule\Module;
use Yii;
use yii\base\Model;
use yii\console\controllers\MigrateController;
use yii\helpers\FileHelper;

class CloneModel extends Model
{
    public $source;
    public $destination;

    public function rules()
    {
        return [
            [['source', 'destination'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'source'  => Yii::t('modules/module', 'Clone source'),
            'destination'       => Yii::t('modules/module', 'Clone destination'),
        ];
    }

    public function run()
    {
        $source = Yii::getAlias('@app' . $this->source);
        if (!file_exists($source) || !is_dir($source)) {
            $this->addError('source', Yii::t('modules/module', 'Source directory not found'));
            return false;
        }
        $dir = basename($source);
        $destination = Yii::getAlias('@app' . $this->destination . '/') . $dir;
        if (file_exists($destination)) {
            $this->addError('destination', Yii::t('modules/module', 'Destination directory already exists'));
            return false;
        }
        return FileHelper::copyDirectory($source, $destination);
    }
}
