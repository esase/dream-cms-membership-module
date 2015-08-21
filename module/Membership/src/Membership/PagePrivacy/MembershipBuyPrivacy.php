<?php

namespace Membership\PagePrivacy;

use User\Service\UserIdentity as UserIdentityService;
use Page\PagePrivacy\PageAbstractPagePrivacy;

class MembershipBuyPrivacy extends PageAbstractPagePrivacy
{
    /**
     * Is allowed view page
     * 
     * @param array $privacyOptions
     * @param boolean $trusted
     * @return boolean
     */
    public function isAllowedViewPage(array $privacyOptions = [], $trustedData = false)
    {
        // additional checking
        if (UserIdentityService::isDefaultUser()) {
            return false;
        }

        return true;
    }
}