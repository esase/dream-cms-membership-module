<?php

return [
    'compatable' => '2.2.4',
    'version' => '1.0.0',
    'vendor' => 'eSASe',
    'vendor_email' => 'alexermashev@gmail.com',
    'description' => 'The module allows you buy different membership levels on the site',
    'system_requirements' => [
        'php_extensions' => [
        ],
        'php_settings' => [
        ],
        'php_enabled_functions' => [
        ],
        'php_version' => null
    ],
    'module_depends' => [
        [
            'module' => 'Payment',
            'vendor' => 'eSASe',
            'vendor_email' => 'alexermashev@gmail.com'
        ]
    ],
    'clear_caches' => [
        'setting'       => true,
        'time_zone'     => false,
        'admin_menu'    => true,
        'js_cache'      => true,
        'css_cache'     => true,
        'layout'        => false,
        'localization'  => false,
        'page'          => false,
        'user'          => false,
        'xmlrpc'        => false
    ],
    'resources' => [
        [
            'dir_name' => 'membership',
            'is_public' => true
        ]
    ],
    'install_sql' => __DIR__ . '/../install/install.sql',
    'install_intro' => 'membership_install_intro',
    'uninstall_sql' => __DIR__ . '/../install/uninstall.sql',
    'uninstall_intro' => 'membership_uninstall_intro',
    'layout_path' => 'membership'
];