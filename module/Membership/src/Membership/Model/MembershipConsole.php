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

use Application\Utility\ApplicationErrorLogger;
use Zend\Db\Sql\Predicate\Predicate as Predicate;
use Zend\Db\ResultSet\ResultSet;
use Exception;

class MembershipConsole extends MembershipBase
{
    /**
     * Mark the membership connection as notified
     *
     * @param integer $connectionId
     * @return string|boolean
     */
    public function markConnectionAsNotified($connectionId)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('membership_level_connection')
                ->set([
                    'notified' => self::MEMBERSHIP_LEVEL_CONNECTION_NOTIFIED
                ])
                ->where([
                   'id' => $connectionId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $result = $statement->execute();
            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        return $result->count() ? true : false;
    }

    /**
     * Get not notified memberships connections
     *
     * @param integer $limit
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getNotNotifiedMembershipsConnections($limit)
    {
        $predicate = new Predicate();
        $time = time();

        $select = $this->select();
        $select->from(['a' => 'membership_level_connection'])
            ->columns([
                'id',
                'user_id',
                'expire_date'
            ])
            ->join(
                ['b' => 'membership_level'],
                'a.membership_id = b.id',
                [
                    'role_id'
                ]
            )
            ->join(
                ['c' => 'acl_role'],
                'b.role_id = c.id',
                [
                    'role_name' => 'name'
                ]
            )
            ->join(
                ['d' => 'user_list'],
                'a.user_id = d.user_id',
                [
                    'nick_name',
                    'email',
                    'language',
                ]
            )
            ->where([
                'a.active' => self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE,
                $predicate->lessThanOrEqualTo('a.notify_date', $time),
                'a.notified' => self::MEMBERSHIP_LEVEL_CONNECTION_NOT_NOTIFIED
            ])
            ->limit($limit);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;

        return $resultSet->initialize($statement->execute());
    }

    /**
     * Get all expired memberships connections
     *
     * @param integer $limit
     * @return array
     */
    public function getExpiredMembershipsConnections($limit)
    {
        $predicate = new Predicate();
        $select = $this->select();
        $select->from(['a' => 'membership_level_connection'])
            ->columns([
                'id',
                'user_id'
            ])
            ->join(
                ['b' => 'membership_level'],
                'a.membership_id = b.id',
                [
                    'role_id'
                ],
                'left'
            )
            ->join(
                ['c' => 'user_list'],
                'a.user_id = c.user_id',
                [
                    'nick_name',
                    'email',
                    'language'
                ]
            )
            ->join(
                ['d' => 'acl_role'],
                'd.id = b.role_id',
                [
                    'role_name' => 'name'
                ],
                'left'
            )
            ->limit($limit)
            ->where([
                'a.active' => self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE,
                $predicate->lessThanOrEqualTo('a.expire_date', time())
            ])
            ->where
                ->or->equalTo('a.active', self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE)
                ->and->isNull('a.membership_id');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }

    /**
     * Delete not active empty connections
     *
     * @return boolean|string
     */
    public function deleteNotActiveEmptyConnections()
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $predicate = new Predicate();
            $delete = $this->delete()
                ->from('membership_level_connection')
                ->where([
                    'active' => self::MEMBERSHIP_LEVEL_CONNECTION_NOT_ACTIVE,
                    $predicate->isNull('membership_id')
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        return true;
    }
}