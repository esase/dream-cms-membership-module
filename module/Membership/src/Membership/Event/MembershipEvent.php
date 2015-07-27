<?php

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