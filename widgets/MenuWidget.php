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
        $order = explode(',', Yii::$app->getModule('module')->params['menuOrder']);
        foreach ($order as $key) {
            $result[trim($key)] = [];
        }
        foreach (\Yii::$app->modules as $module) {
            $params = is_object($module)
                ? $module->params
                : (isset($module['params']) ? $module['params'] : []);
            if (!isset($params['menu'])) {
                continue;
            }

            if (isset($result[$params['menu']['label']]) && ($oldItem = $result[$params['menu']['label']])) {
                $oldItems = isset($oldItem['items']) ? $oldItem['items'] : $oldItem;
                $newItems = isset($params['menu']['items']) ? $params['menu']['items'] : $params['menu'];
                $params['menu']['items'] = ArrayHelper::merge($oldItems, $newItems);
            }
            $result[$params['menu']['label']] = $params['menu'];
        }
        array_walk($result, function($v, $k) use(&$result) { if(!$v) unset($result[$k]);});
        $this->items = array_values($result);
    }
}
