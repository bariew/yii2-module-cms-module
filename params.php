<?php
$localPath = __DIR__ . DIRECTORY_SEPARATOR . 'params-local.php';
return array_merge([
    'menu'  => [
        'label'    => 'Settings',
        'items' => [
            ['label' => 'Modules', 'url' => ['/module/item/index']],
        ]
    ],
    'renderMenu' => 1,
    'menuOrder' => ''
], (file_exists($localPath) ? require $localPath : []));