<?php

return [
    'controllers' => [
        'invokables' => [
            'membership-administration' => 'Membership\Controller\MembershipAdministrationController',
            'membership-console' => 'Membership\Controller\MembershipConsoleController',
            'membership-ajax' => 'Membership\Controller\MembershipAjaxController'
        ]
    ],
    'router' => [
        'routes' => [
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'membership clean connections' => [
                    'options' => [
                        'route'    => 'membership clean expired connections [--verbose|-v]',
                        'defaults' => [
                            'controller' => 'membership-console',
                            'action'     => 'cleanExpiredMembershipsConnections'
                        ]
                    ]
                ]
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