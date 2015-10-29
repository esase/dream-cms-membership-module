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
namespace Membership\View\Widget;

use User\Service\UserIdentity as UserIdentityService;
use Membership\Model\MembershipBase as MembershipBaseModel;
use Acl\Model\AclBase as AclBaseModel;
use Application\Utility\ApplicationCsrf as ApplicationCsrfUtility;

class MembershipUserLevelsWidget extends MembershipAbstractWidget
{
    /**
     * User Model instance
     *
     * @var \User\Model\UserBase
     */
    protected $userModel;

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
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        if (UserIdentityService::isGuest()) {
            return false;
        }

        $userId = UserIdentityService::getCurrentUserIdentity()['user_id'];

        // process post actions
        if ($this->getRequest()->isPost() &&
                ApplicationCsrfUtility::isTokenValid($this->getRequest()->getPost('csrf'))) {

            $action = $this->getRequest()->getPost('action');

            if ($action) {
                switch($action) {
                    case 'delete_membership' :
                        $this->deleteMembership($this->getRequest()->getPost('id', -1));
                        break;

                    default :
                }
            }
        }
        // get a pagination page number
        $pageParamName = 'page_' . $this->widgetConnectionId;
        $page = $this->getView()->applicationRoute()->getQueryParam($pageParamName , 1);
        $wrapperId = 'purchased-memberships-wrapper';
        $count = (int) $this->getWidgetSetting('membership_user_list_items_count');

        // get data list
        $dataList = $this->getView()->partial('partial/data-list', [
            'filter_form' => false,
            'ajax' => [
                'wrapper_id' => $wrapperId,
                'widget_connection' => $this->widgetConnectionId,
                'widget_position' => $this->widgetPosition
            ],
            'paginator' => $this->getModel()->getUserMembershipConnections($userId, $page, $count),
            'paginator_order_list_show' => false,
            'paginator_order_list' => [
            ],
            'paginator_per_page_show' => false,
            'paginator_page_query' => $pageParamName,
            'unit' => 'membership/partial/_membership-user-unit',
            'unit_params' => [
                'items_width_medium' => $this->getWidgetSetting('membership_user_list_item_width_medium'),
                'items_width_small' => $this->getWidgetSetting('membership_user_list_item_width_small'),
                'items_width_extra_small' => $this->getWidgetSetting('membership_user_list_item_width_extra_small'),
            ],
            'uniform_height' => '#'. $wrapperId . ' .membership-info',
            'per_page' => $count
        ]);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $dataList;
        }

        return $this->getView()->partial('membership/widget/membership-user', [
            'csrf_token' => ApplicationCsrfUtility::getToken(),
            'widget_url' => $this->getWidgetConnectionUrl(),
            'membership_wrapper' => $wrapperId,
            'data' => $dataList
        ]);
    }

    /**
     * Delete membership
     *
     * @param integer $membershipId
     * @return void
     */
    protected function deleteMembership($membershipId)
    {
        $userId = UserIdentityService::getCurrentUserIdentity()['user_id'];

        // get a membership level info
        if (null !== ($connectionInfo =
                $this->getModel()->getMembershipConnectionInfo($membershipId, $userId))) {

            // delete the membership level
            if (false !== ($deleteResult =
                    $this->getModel()->deleteMembershipConnection($connectionInfo['id'], false))) {

                if ($connectionInfo['active'] ==
                        MembershipBaseModel::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE) {

                    // get a next membership connection
                    $nextConnection = $this->getModel()->getMembershipConnectionFromQueue($userId);

                    $nextRoleId = $nextConnection
                        ? $nextConnection['role_id']
                        : AclBaseModel::DEFAULT_ROLE_MEMBER;

                    $nextRoleName = $nextConnection
                        ? $nextConnection['role_name']
                        : AclBaseModel::DEFAULT_ROLE_MEMBER_NAME;

                    // change the user's role
                    if (true === ($result = $this->getUserModel()->
                            editUserRole($userId, $nextRoleId, $nextRoleName, $connectionInfo, true))) {

                        // activate the next membership connection
                        if ($nextConnection) {
                            $this->getModel()->activateMembershipConnection($nextConnection['id']);
                        }
                    }
                }
            }
        }
    }
}