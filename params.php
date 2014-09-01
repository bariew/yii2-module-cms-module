<?php
$localPath = __DIR__ . DIRECTORY_SEPARATOR . 'params-local.php';
return array_merge([
    'menu'  => [
        'label'    => 'Settings',
        'items' => [
            [
                'label' => 'Modules',
                'items' => [
                    ['label' => 'Installed', 'url' => ['/module/item/index']],
                    ['label' => 'Search', 'url' => ['/module/item/search']],
                    ['label' => 'Migrate all', 'url' => ['/module/item/migrate']],
                ]
            ],
        ]
    ],
    'renderMenu' => 1,
    'menuOrder' => ''
], (file_exists($localPath) ? require $localPath : []));