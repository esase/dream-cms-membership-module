<?php

return [
    'modules' => [
        'Application',
        'User',
        'XmlRpc',
        'Membership',
        'Payment'
    ],
    'module_listener_options' => [
        'config_glob_paths' => [
            APPLICATION_ROOT . 'config/autoload/{,*.}{global,local,development}.php'
        ],
        'module_paths' => [
            './module',
            './vendor'
        ]
    ]
];
