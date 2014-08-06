<?php

namespace bariew\moduleModule\widgets;
use Yii;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;

class MenuWidget extends Nav
{
    public function init() 
    {
        parent::init();
        $this->setItems();
    }
    
    private function setItems()
    {
        $result = [];
        foreach (\Yii::$app->modules as $module) {
            $params = is_object($module)
                ? $module->params
                : (isset($module['params']) ? $module['params'] : []);
            if (!isset($params['menu'])) {
                continue;
            }

            $oldItems = isset($result[$params['menu']['label']])
                ? $result[$params['menu']['label']] : [];
            $result[$params['menu']['label']] = ArrayHelper::merge($oldItems, $params['menu']);
        }
        ksort($result);
        $this->items = array_values($result);
        //print_r($this->items);exit;
    }
}
