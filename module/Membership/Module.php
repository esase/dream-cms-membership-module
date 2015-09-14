<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
namespace Membership;

use Localization\Event\LocalizationEvent;
use User\Event\UserEvent;
use User\Model\UserBase as UserBaseModel;
use Membership\Event\MembershipEvent;
use Acl\Event\AclEvent;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    /**
     * Init
     *
     * @param \Zend\ModuleManager\ModuleManagerInterface $moduleManager
     * @return void
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = MembershipEvent::getEventManager();

        // someone forced a user's role, and now we must clean all the user's membership queue
        $eventManager->attach(UserEvent::EDIT_ROLE, function ($e) use ($moduleManager) {
            if ($e->getParam('user_id') != UserBaseModel::DEFAULT_SYSTEM_ID) {
                $this->deleteUserMembershipLevels($moduleManager, $e->getParam('object_id'));
            }
        });

        // TODO: Delete them via the delete service
        // listen the delete acl role event
        $eventManager->attach(AclEvent::DELETE_ROLE, function ($e) use ($moduleManager) {
            $this->deleteMembershipLevels($moduleManager, $e->getParam('object_id'));
        });
    }

    /**
     * Delete membership levels
     *
     * @param \Zend\ModuleManager\ModuleManagerInterface $moduleManager
     * @param integer $roleId
     * @return void
     */
    protected function deleteMembershipLevels(ModuleManagerInterface $moduleManager, $roleId)
    {
        $model = $moduleManager->getEvent()
            ->getParam('ServiceManager')
            ->get('Application\Model\ModelManager')
            ->getInstance('Membership\Model\MembershipBase');

        // delete membership levels
        if (null != ($membershipLevels = $model->getAllMembershipLevels($roleId))) {
            foreach ($membershipLevels as $levelInfo) {
                $model->deleteRole($levelInfo, true);
            }
        }
    }

    /**
     * Delete user's membership levels
     *
     * @param \Zend\ModuleManager\ModuleManagerInterface $moduleManager
     * @param integer $userId
     * @return void
     */
    protected function deleteUserMembershipLevels(ModuleManagerInterface $moduleManager, $userId)
    {
        $model = $moduleManager->getEvent()
            ->getParam('ServiceManager')
            ->get('Application\Model\ModelManager')
            ->getInstance('Membership\Model\MembershipBase');

        // delete all user's connections
        if (null != ($connections = $model->getAllUserMembershipConnections($userId))) {
            foreach ($connections as $connection) {
                // delete the connection
                if (false === ($deleteResult = $model->deleteMembershipConnection($connection->id))) {
                    break;
                }
            }
        }
    }

    /**
     * Return auto loader config array
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php'
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ]
            ]
        ];
    }

    /**
     * Return service config array
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
        ];
    }

    /**
     * Init view helpers
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'membershipLevelWidget' => 'Membership\View\Widget\MembershipLevelWidget',
                'membershipUserLevelsWidget' => 'Membership\View\Widget\MembershipUserLevelsWidget',
                'membershipImageUrl' => 'Membership\View\Helper\MembershipImageUrl'
            ],
            'factories' => [
            ]
        ];
    }

    /**
     * Return path to config file
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get console usage info
     *
     * @param \Zend\Console\Adapter\AdapterInterface $console
     * @return array
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            // describe available commands
            'membership clean expired connections [--verbose|-v]' => 'Clean expired membership connections',
            // describe expected parameters
            ['--verbose|-v', '(optional) turn on verbose mode']
        ];
    }
}