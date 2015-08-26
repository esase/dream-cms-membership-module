<?php

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