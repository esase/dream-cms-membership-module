<?php

namespace Membership\View\Widget;

use User\Service\UserIdentity as UserIdentityService;
use Membership\Model\MembershipBase as MembershipBaseModel;
use Acl\Model\AclBase as AclBaseModel;

class MembershipUserLevelsWidget extends MembershipAbstractWidget
{
    /**
     * User Model instance
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
        if ($this->getRequest()->isPost()) {
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