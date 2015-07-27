<?php

return [
    'controllers' => [
        'invokables' => [
            'membership-administration' => 'Membership\Controller\MembershipAdministrationController'
        ]
    ],
    'router' => [
        'routes' => [
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
            ]
        ]
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'getText',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain'  => 'default'
            ]
        ]
    ],
    'view_helpers' => [
        'invokables' => [
        ]
    ]
];