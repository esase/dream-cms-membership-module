<?php

namespace Membership\Model;

use Zend\Db\Sql\Predicate\Predicate as Predicate;

/*
use Exception;
use Application\Utility\ErrorLogger;
use Zend\Db\ResultSet\ResultSet;
*/

class MembershipConsole extends MembershipBase
{
    /**
     * Mark the membership connection as notified
     *
     * @param integer $connectionId
     * @return boolean
     */
    /*public function markConnectionAsNotified($connectionId)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('membership_level_connection')
                ->set(array(
                    'notified' => self::MEMBERSHIP_LEVEL_CONNECTION_NOTIFIED,
                ))
                ->where(array(
                   'id' => $connectionId
                ));

            $statement = $this->prepareStatementForSqlObject($update);
            $result = $statement->execute();
            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ErrorLogger::log($e);

            return $e->getMessage();
        }

        return $result->count() ? true : false;
    }*/

    /**
     * Get not notified memberships connections
     *
     * @return object
     */
    /*public function getNotNotifiedMembershipsConnections()
    {
        $predicate = new Predicate();
        $time = time();

        $select = $this->select();
        $select->from(array('a' => 'membership_level_connection'))
            ->columns(array(
                'id',
                'user_id',
                'expire_date'
            ))
            ->join(
                array('b' => 'membership_level'),
                'a.membership_id = b.id',
                array(
                    'role_id'
                )
            )
            ->join(
                array('c' => 'application_acl_role'),
                'b.role_id = c.id',
                array(
                    'role_name' => 'name'
                )
            )
            ->join(
                array('d' => 'user_list'),
                'a.user_id = d.user_id',
                array(
                    'nick_name',
                    'email',
                    'language',
                )
            )
            ->where(array(
                'a.active' => self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE,
                $predicate->lessThanOrEqualTo('a.notify_date', $time),
                'a.notified' => self::MEMBERSHIP_LEVEL_CONNECTION_NOT_NOTIFIED
            ));

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        return $resultSet->initialize($statement->execute());
    }*/

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