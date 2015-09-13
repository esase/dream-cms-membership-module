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
namespace Membership\Model;

use Application\Service\ApplicationSetting as SettingService;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;

class MembershipWidget extends MembershipBase
{
    /**
     * Get user's membership connections
     *
     * @param integer $userId
     * @param integer $page
     * @param integer $perPage
     * @return \Zend\Paginator\Paginator
     */
    public function getUserMembershipConnections($userId, $page = 1, $perPage = 0)
    {
        $select = $this->select();
        $select->from(['a' => 'membership_level_connection'])
            ->columns([
                'id',
                'active',
                'expire_date',
                'expire_value'
            ])
            ->join(
                ['b' => 'membership_level'],
                'a.membership_id = b.id',
                [
                    'title',
                    'role_id',
                    'cost',
                    'lifetime',
                    'expiration_notification',
                    'description',
                    'language',
                    'image'
                ]
            )->where([
                'user_id' => $userId
            ])
            ->order('a.id');

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }
}