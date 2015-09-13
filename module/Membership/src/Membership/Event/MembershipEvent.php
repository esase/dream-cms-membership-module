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
namespace Membership\Event;

use Application\Event\ApplicationAbstractEvent;
use User\Service\UserIdentity as UserIdentityService;

class MembershipEvent extends ApplicationAbstractEvent
{
    /**
     * Add membership role event
     */
    const ADD_MEMBERSHIP_ROLE = 'add_membership_role';

    /**
     * Edit membership role event
     */
    const EDIT_MEMBERSHIP_ROLE = 'edit_membership_role';

    /**
     * Delete membership role event
     */
    const DELETE_MEMBERSHIP_ROLE = 'delete_membership_role';

    /**
     * Delete membership connection event
     */
    const DELETE_MEMBERSHIP_CONNECTION = 'delete_membership_connection';

    /**
     * Activate membership connection event
     */
    const ACTIVATE_MEMBERSHIP_CONNECTION = 'activate_membership_connection';

    /**
     * Fire activate membership connection event
     *
     * @param integer $connectionId
     * @return void
     */
    public static function fireActivateMembershipConnectionEvent($connectionId)
    {
        // event's description
        $eventDesc = 'Event - Membership connection activated by the system';
        self::fireEvent(self::ACTIVATE_MEMBERSHIP_CONNECTION, $connectionId, self::getUserId(true), $eventDesc, [
            $connectionId 
        ]);
    }

    /**
     * Fire delete membership connection event
     *
     * @param integer $connectionId
     * @param boolean $isSystemEvent
     * @return void
     */
    public static function fireDeleteMembershipConnectionEvent($connectionId, $isSystemEvent = true)
    {
        // event's description
        $eventDesc = $isSystemEvent
            ? 'Event - Membership connection deleted by the system'
            : 'Event - Membership connection deleted by user';

        $eventDescParams = $isSystemEvent
            ? [$connectionId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $connectionId];

        self::fireEvent(self::DELETE_MEMBERSHIP_CONNECTION, 
                $connectionId, self::getUserId($isSystemEvent), $eventDesc, $eventDescParams);
    }

    /**
     * Fire delete membership role event
     *
     * @param integer $membershipRoleId
     * @param boolean $isSystemEvent
     * @return void
     */
    public static function fireDeleteMembershipRoleEvent($membershipRoleId, $isSystemEvent = false)
    {
        // event's description
        $eventDesc = $isSystemEvent
            ? 'Event - Membership role deleted by the system'
            : (UserIdentityService::isGuest() ? 'Event - Membership role deleted by guest'
                    : 'Event - Membership role deleted by user');

        $eventDescParams = $isSystemEvent
            ? [$membershipRoleId]
            : (UserIdentityService::isGuest() ? [$membershipRoleId]
                    : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $membershipRoleId]);

        self::fireEvent(self::DELETE_MEMBERSHIP_ROLE, $membershipRoleId, self::getUserId($isSystemEvent), $eventDesc, $eventDescParams);
    }

    /**
     * Fire edit membership role event
     *
     * @param integer $membershipRoleId
     * @return void
     */
    public static function fireEditMembershipRoleEvent($membershipRoleId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Membership role edited by guest'
            : 'Event - Membership role edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$membershipRoleId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $membershipRoleId];

        self::fireEvent(self::EDIT_MEMBERSHIP_ROLE, 
                $membershipRoleId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add membership role event
     *
     * @param integer $membershipRoleId
     * @return void
     */
    public static function fireAddMembershipRoleEvent($membershipRoleId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Membership role added by guest'
            : 'Event - Membership role added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$membershipRoleId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $membershipRoleId];

        self::fireEvent(self::ADD_MEMBERSHIP_ROLE, 
                $membershipRoleId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }
}