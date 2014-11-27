<?php
/**
 * Module class file.
 * @copyright (c) 2014, Galament
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
                    'items' => [
                        ['label' => 'Installed', 'url' => ['/module/item/index']],
                        ['label' => 'Migrate all', 'url' => ['/module/item/migrate']],
                    ]
                ],
            ]
        ],
        'renderMenu' => 1,
        'menuOrder' => ''
    ];
}
