<?php

return [
    'controllers' => [
        'invokables' => [
            'memberships-administration' => 'Membership\Controller\MembershipAdministrationController',
            'memberships-console' => 'Membership\Controller\MembershipConsoleController',
            'memberships-ajax' => 'Membership\Controller\MembershipAjaxController'
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
                            'controller' => 'memberships-console',
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