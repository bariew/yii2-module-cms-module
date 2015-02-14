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
                    'items' => [
                        [
                            'label' => 'List',
                            'url' => ['/module/item/index'],                            
                        ],
                        [
                            'label' => 'Clone',
                            'url' => ['/module/clone/create'],
                        ],
                        [
                            'label' => 'Snapshot',
                            'url' => ['/module/snapshot/create'],
                        ],
                    ]
                ],
            ]
        ],
        'menuOrder' => ''
    ];
}
