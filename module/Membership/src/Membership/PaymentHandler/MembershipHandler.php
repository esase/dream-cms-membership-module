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
namespace Membership\PaymentHandler;

use Payment\Handler\PaymentAbstractHandler;
use User\Model\UserBase as UserBaseModel;
use User\Service\UserIdentity as UserIdentityService;

class MembershipHandler extends PaymentAbstractHandler 
{
    /**
     * Model instance
     *
     * @var \Membership\Model\MembershipBase
     */
    protected $model;

    /**
     * User model instance
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
            $this->userModel = $this->serviceLocator
                ->get('Application\Model\ModelManager')
                ->getInstance('User\Model\UserBase');
        }

        return $this->userModel;
    }

    /**
     * Get model
     *
     * @return \Membership\Model\MembershipBase
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->serviceLocator
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipBase');
        }

        return $this->model;
    }

    /**
     * Get the item's info
     *
     * @param integer $id
     * @return array
     *      string|integer slug - optional
     *      string title - required
     *      float|array cost - required
     *      float discount - optional
     *      integer count - required (only for countable modules)
     *      array extra_options - optional (a form array notation)
     */
    public function getItemInfo($id)
    {
        // get membership info
        if (null == ($roleInfo = $this->getModel()->getRoleInfo($id, true))) {
            return;
        }

        return [
            'slug' => null,
            'title' => $roleInfo['title'],
            'cost' => $roleInfo['cost'],
            'discount' => $this->getDiscount($id)
        ];
    }

    /**
     * Get the items' extra options
     *
     * @param integer $id
     * @return array
     */
    public function getItemExtraOptions($id)
    {}

    /**
     * Get discount
     *
     * @param integer $id
     * @return float
     */
    public function getDiscount($id)
    {}

    /**
     * Clear the discount
     *
     * @param integer $id
     * @param float $discount
     * @return void
     */
    public function clearDiscount($id, $discount)
    {}

    /**
     * Return back the discount
     *
     * @param integer $id
     * @param float $discount
     * @return void
     */
    public function returnBackDiscount($id, $discount)
    {}

    /**
     * Decrease the item's count 
     *
     * @param integer $id
     * @param integer $count
     * @return void
     */
    public function decreaseCount($id, $count)
    {}

    /**
     * Set the item as paid
     *
     * @param integer $id
     * @param array $transactionInfo
     *      integer id
     *      string slug
     *      integer user_id
     *      string first_name
     *      string last_name
     *      string phone
     *      string address
     *      string email
     *      integer currency
     *      integer payment_type
     *      integer discount_cupon
     *      string currency_code
     *      string payment_name 
     * @return void
     */
    public function setPaid($id, array $transactionInfo)
    {
        // the default user cannot buy any membership levels,
        // he(she) must stays as a default user with the admin role
        if ($transactionInfo['user_id'] == UserBaseModel::DEFAULT_USER_ID
                || null == ($roleInfo = $this->getModel()->getRoleInfo($id, true))) {

            return;
        }

        // get a user's membership connections
        $result = $this->getModel()->getAllUserMembershipConnections($transactionInfo['user_id']);
        $activateConnection = count($result) ? false : true;

        // add a new membership connection
        $connectionId = $this->getModel()->addMembershipConnection(
                $transactionInfo['user_id'], $roleInfo['id'], $roleInfo['lifetime'], $roleInfo['expiration_notification']);

        // activate the membership connection
        if (is_numeric($connectionId) && $activateConnection) {
            // change the user's role
            $userInfo = UserIdentityService::getUserInfo($transactionInfo['user_id']);

            if (true === ($result = $this->getUserModel()->
                    editUserRole($transactionInfo['user_id'],$roleInfo['role_id'], $roleInfo['role_name'], (array) $userInfo, true))) {

                // activate the membership connection
                $this->getModel()->activateMembershipConnection($connectionId);
            }
        }
    }
}