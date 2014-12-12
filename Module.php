<?php
/**
 * Module class file.
 * @copyright (c) 2014, Bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\moduleModule;

/**
 * Module class.
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Module extends \yii\base\Module
{
    public $params = [
        'menu'  => [
            'label'    => 'Settings',
            'items' => [
                [
                    'label' => 'Modules',
                    'url' => ['/module/item/index'],
                ],
            ]
        ],
        'menuOrder' => ''
    ];
}
