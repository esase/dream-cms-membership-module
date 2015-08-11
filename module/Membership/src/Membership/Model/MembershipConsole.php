<?php

namespace Membership\Model;

use Zend\Db\Sql\Predicate\Predicate as Predicate;
use Zend\Db\ResultSet\ResultSet;
use Application\Utility\ApplicationErrorLogger;
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
     * @return Zend\Db\ResultSet\ResultSet
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
                ]
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
                ]
            )
            ->where([
                'a.active' => self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE,
                $predicate->lessThanOrEqualTo('a.expire_date', time())
            ])
            ->limit($limit);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }
}