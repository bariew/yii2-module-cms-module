<?php

namespace bariew\moduleModule\widgets;
use Yii;
use bariew\dropdown\Nav;
use yii\helpers\ArrayHelper;

class MenuWidget extends Nav
{
    protected function createItems($items)
    {
        $result = [];
        $order = explode(',', Yii::$app->getModule('module')->params['menuOrder']);
        foreach ($order as $key) {
            $result[trim($key)] = [];
        }
        foreach (\Yii::$app->modules as $id => $options) {
            $module = Yii::$app->getModule($id);
            $params = $module->params;
            if (!isset($params['menu']) || !isset($params['menu']['label'])) {
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
        return array_values($result);
    }
}
