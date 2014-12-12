<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/25/14
 * Time: 6:20 PM
 */

namespace bariew\moduleModule\models;


use app\config\ConfigManager;
use Yii;

use yii\base\Model;
use bariew\moduleModule\models\Item;

class Param extends Model
{
    /**
     * @var Item
     */
    public $item;
    protected $_attributes = [];
    public $serializedAttributes = [];

    public function validate($attributeNames = NULL, $clearErrors = true)
    {
        foreach ($this->_attributes as $attribute => $value) {
            if ($this->isSerializable($attribute)) {
                $data = json_decode($value, true);
                if (!is_array($data) || json_last_error()) {
                    $this->addError($attribute, "Not valid json");
                    return false;
                }
            }
        }

        return parent::validate($attributeNames = NULL, $clearErrors = true);
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $attributes = [];
        foreach ($this->_attributes as $attribute => $value) {
            $attributes[$attribute] = $this->isSerializable($attribute)
                ? json_decode($value, true) : $value;
        }
        return ConfigManager::set(['modules', $this->item->moduleName, 'params'], $attributes);
    }

    public function init()
    {
        $attributes = Yii::$app->getModule($this->item->moduleName)->params;
        foreach ($attributes as $name => $value) {
            if (is_array($value)) {
                $this->serializedAttributes[] = $name;
                $value = json_encode($value);
            }
            $this->_attributes[$name] = $value;
        }
        parent::init();
    }

    public function attributes()
    {
        return array_keys($this->_attributes);
    }

    /**
     * Returns the attribute names that are safe to be massively assigned in the current scenario.
     * @return string[] safe attribute names
     */
    public function safeAttributes()
    {
        return $this->attributes();
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        return $this->_attributes[$name] = $value;
    }

    public function isSerializable($attribute)
    {
        return in_array($attribute, $this->serializedAttributes);
    }
} 