<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/6/14
 * Time: 5:20 PM
 */

namespace bariew\moduleModule\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class Menu extends Model
{
    public $active;

    public function rules()
    {
        return [
            [['active'], 'integer']
        ];
    }

    public static $settingsPath = '@app/config/local/main.php';

    public static function isActive()
    {
        return !isset(Yii::$app->params['adminMenu']) || !empty(Yii::$app->params['adminMenu']['active']);
    }

    public function init()
    {
        parent::init();
        $this->active = self::isActive() ? 1 : 0;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $path = Yii::getAlias(self::$settingsPath);
        $settings = file_exists($path) ? require $path : [];
        $settings['params']['adminMenu'] = $this->attributes;
        Yii::$app->params = $settings['params'];
        $content = '<?php return ' . var_export($settings, true). ';';
        return file_put_contents($path, $content);
    }


} 