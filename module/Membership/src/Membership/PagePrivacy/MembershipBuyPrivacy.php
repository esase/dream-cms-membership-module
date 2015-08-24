<?php

namespace Membership\PagePrivacy;

use User\Service\UserIdentity as UserIdentityService;
use Page\PagePrivacy\PageAbstractPagePrivacy;
use Acl\Service\Acl as AclService;

class MembershipBuyPrivacy extends PageAbstractPagePrivacy
{
    /**
     * Is allowed view page
     * 
     * @param array $privacyOptions
     * @param boolean $trustedData
     * @return boolean
     */
    public function isAllowedViewPage(array $privacyOptions = [], $trustedData = false)
    {
        // check a permission
        if (UserIdentityService::isDefaultUser()
                || !AclService::checkPermission('memberships_view_buy_page', false)) {

            return false;
        }

        return true;
    }
}