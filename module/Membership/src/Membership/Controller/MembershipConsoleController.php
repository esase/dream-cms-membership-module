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
namespace Membership\Controller;

use Application\Controller\ApplicationAbstractBaseConsoleController;
use Acl\Model\AclBase as AclBaseModel;
use Localization\Service\Localization as LocalizationService;
use Application\Utility\ApplicationEmailNotification;
use Application\Service\ApplicationServiceLocator as ApplicationServiceLocatorService;

class MembershipConsoleController extends ApplicationAbstractBaseConsoleController
{
    /**
     * Items limit
     */
    const ITEMS_LIMIT = 500;

    /**
     * Model instance
     *
     * @var \Membership\Model\MembershipConsole
     */
    protected $model;

    /**
     * User Model instance
     *
     * @var \User\Model\UserBase
     */
    protected $userModel;

    /**
     * Get model
     *
     * @return \Membership\Model\MembershipConsole
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipConsole');
        }

        return $this->model;
    }

    /**
     * Get user model
     *
     * @return \User\Model\UserBase
     */
    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('User\Model\UserBase');
        }

        return $this->userModel;
    }

    /**
     * Clean expired memberships connections
     */
    public function cleanExpiredMembershipsConnectionsAction()
    {
        $request = $this->getRequest();

        $deletedConnections  = 0;
        $notifiedConnections = 0;

        // get a list of expired memberships connections
        if (null != ($expiredConnections = $this->
                getModel()->getExpiredMembershipsConnections(self::ITEMS_LIMIT))) {

            // process expired memberships connections
            foreach ($expiredConnections as $connectionInfo) {
                // delete the connection
                if (false === ($deleteResult = 
                        $this->getModel()->deleteMembershipConnection($connectionInfo['id']))) {

                    break;
                }

                // get a next membership connection
                $nextConnection = $this->getModel()->getMembershipConnectionFromQueue($connectionInfo['user_id']);
                $nextRoleId = $nextConnection
                    ? $nextConnection['role_id']
                    : AclBaseModel::DEFAULT_ROLE_MEMBER;

                $nextRoleName = $nextConnection
                    ? $nextConnection['role_name']
                    : AclBaseModel::DEFAULT_ROLE_MEMBER_NAME;

                // change the user's role 
                if (true === ($result = $this->getUserModel()->
                        editUserRole($connectionInfo['user_id'], $nextRoleId, $nextRoleName, $connectionInfo, true))) {

                    // activate the next membership connection
                    if ($nextConnection) {
                        $this->getModel()->activateMembershipConnection($nextConnection['id']);
                    }
                }

                $deletedConnections++;
            }
        }

        // get list of not notified memberships connections
        if ((int) $this->applicationSetting('membership_expiring_send')) {
            if (null != ($notNotifiedConnections = $this->
                    getModel()->getNotNotifiedMembershipsConnections(self::ITEMS_LIMIT))) {

                // process not notified memberships connections
                foreach ($notNotifiedConnections as $connectionInfo) {
                    if (false === ($markResult = 
                            $this->getModel()->markConnectionAsNotified($connectionInfo['id']))) {

                        break;
                    }

                    // send a notification about membership expiring
                    $notificationLanguage = $connectionInfo['language']
                        ? $connectionInfo['language'] // we should use the user's language
                        : LocalizationService::getDefaultLocalization()['language'];

                    $locale = LocalizationService::getLocalizations()[$notificationLanguage]['locale'];

                    ApplicationEmailNotification::sendNotification($connectionInfo['email'],
                            $this->applicationSetting('membership_expiring_send_title', $notificationLanguage),
                            $this->applicationSetting('membership_expiring_send_message', $notificationLanguage), [
                                'find' => [
                                    'RealName',
                                    'Role',
                                    'ExpireDate'
                                ],
                                'replace' => [
                                    $connectionInfo['nick_name'],
                                    ApplicationServiceLocatorService::getServiceLocator()->
                                            get('Translator')->translate($connectionInfo['role_name'], 'default', $locale),

                                    ApplicationServiceLocatorService::getServiceLocator()->
                                            get('viewhelpermanager')->get('applicationDate')->__invoke($connectionInfo['expire_date'], [], $locale)
                                ]
                            ]);

                    $notifiedConnections++;
                }
            }
        }

        // delete not active empty connections
        $this->getModel()->deleteNotActiveEmptyConnections();

        $verbose = $request->getParam('verbose');

        if (!$verbose) {
            return 'All expired  membership connections have been deleted.' . "\n";
        }

        $result  = $deletedConnections  . ' membership connections  have been deleted.'. "\n";
        $result .= $notifiedConnections . ' membership connections  have been notified.'. "\n";

        return $result;
    }
}