<?php

namespace Membership\Model;

use Membership\Event\MembershipEvent;
use Application\Utility\ApplicationErrorLogger;
use Application\Model\ApplicationAbstractBase;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\ResultSet\ResultSet;
use Payment\Model\PaymentBase as PaymentBaseModel;
use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Membership\Exception\MembershipException;
use Exception;

class MembershipBase extends ApplicationAbstractBase
{
    /**
     * Seconds in a day
     */
    const SECONDS_IN_DAY = 86400;

    /**
     * Membership level active status flag
     */
    const MEMBERSHIP_LEVEL_STATUS_ACTIVE = 1;

    /**
     * Membership level not active status flag
     */
    const MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE = 0;

    /**
     * Membership level connection active flag
     */
    const MEMBERSHIP_LEVEL_CONNECTION_ACTIVE = 1;

    /**
     * Membership level connection not active flag
     */
    const MEMBERSHIP_LEVEL_CONNECTION_NOT_ACTIVE = 0;

    /**
     * Membership level connection not notified
     */
    const MEMBERSHIP_LEVEL_CONNECTION_NOT_NOTIFIED = 0;

    /**
     * Membership level connection notified
     */
    const MEMBERSHIP_LEVEL_CONNECTION_NOTIFIED = 1;

    /**
     * Images directory
     * @var string
     */
    protected static $imagesDir = 'membership/';

    /**
     * Delete an membership's image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deleteImage($imageName)
    {
        return FileSystemUtility::deleteResourceFile($imageName, self::$imagesDir);
    }

    /**
     * Get images directory name
     *
     * @return string
     */
    public static function getImagesDir()
    {
        return self::$imagesDir;
    }

    /**
     * Get membership levels
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @param array $filters
     *      string title
     *      float cost
     *      integer lifetime
     *      integer role
     *      integer active
     * @return Zend\Paginator\Paginator
     */
    public function getMembershipLevels($page = 1, $perPage = 0, $orderBy = null, $orderType = null, array $filters = [])
    {
        $orderFields = [
            'id',
            'title',
            'cost',
            'lifetime',
            'active',
            'subscribers'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from(['a' => 'membership_level'])
            ->columns([
                'id',
                'title',
                'cost',
                'lifetime',
                'active',
                'description',
                'image',
                'role_id'
            ])
            ->join(
                ['b' => 'membership_level_connection'],
                'b.membership_id = a.id',
                [
                    'subscribers' => new Expression('count(b.id)'),
                ],
                'left'
            )
            ->join(
                ['c' => 'acl_role'],
                'a.role_id = c.id',
                [
                    'role' => 'name'
                ]
            )
            ->join(
                ['d' => 'payment_currency'],
                new Expression('d.primary_currency = ?', [PaymentBaseModel::PRIMARY_CURRENCY]),
                array(
                    'currency' => 'code'
                )
            )
            ->group('a.id')
            ->order($orderBy . ' ' . $orderType)
            ->where([
                'a.language' => $this->getCurrentLanguage()
            ]);

        // filter by a title
        if (!empty($filters['title'])) {
            $select->where([
                'a.title' => $filters['title']
            ]);
        }

        // filter by a cost
        if (!empty($filters['cost'])) {
            $select->where([
                'a.cost' => $filters['cost']
            ]);
        }

        // filter by a lifetime
        if (!empty($filters['lifetime'])) {
            $select->where([
                'a.lifetime' => $filters['lifetime']
            ]);
        }

        // filter by a role
        if (!empty($filters['role'])) {
            $select->where([
                'c.id' => $filters['role']
            ]);
        }

        // filter by a active
        if (isset($filters['active']) && $filters['active'] != null) {
            $select->where([
                'a.active' => ((int) $filters['active'] == self::MEMBERSHIP_LEVEL_STATUS_ACTIVE
                    ? $filters['active']
                    : self::MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE)
            ]);
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

    /**
     * Get the role info
     *
     * @param integer $id
     * @param boolean $onlyActive
     * @param boolean $currentLanguage
     * @return array
     */
    public function getRoleInfo($id, $onlyActive = false, $currentLanguage = false)
    {
        $select = $this->select();
        $select->from(['a' => 'membership_level'])
            ->columns([
                'id',
                'title',
                'role_id',
                'cost',
                'lifetime',
                'expiration_notification',
                'description',
                'language',
                'image',
                'active'
            ])
            ->join(
                ['b' => 'membership_level_connection'],
                'b.membership_id = a.id',
                [
                    'subscribers' => new Expression('count(b.id)'),
                ],
                'left'
            )
            ->join(
                ['c' => 'acl_role'],
                'c.id = a.role_id',
                [
                    'role_name' => 'name'
                ]
            )
            ->where([
                'a.id' => $id
            ])
            ->group('a.id');

        if ($onlyActive) {
            $select->where([
                'a.active' => self::MEMBERSHIP_LEVEL_STATUS_ACTIVE
            ]);
        }

        if ($currentLanguage) {
            $select->where([
                'a.language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }

    /**
     * Delete the role
     *
     * @param array $roleInfo
     *      integer id required
     *      string image required
     * @param boolean $isSystem
     * @throws MembershipException
     * @return boolean|string
     */
    public function deleteRole($roleInfo, $isSystem = false)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('membership_level')
                ->where([
                    'id' => $roleInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            // delete the image
            if ($roleInfo['image']) {
                if (true !== ($imageDeleteResult = $this->deleteImage($roleInfo['image']))) {
                    throw new MembershipException('Image deleting failed');
                }
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        if ($result->count()) {
            // fire the delete membership role event
            MembershipEvent::fireDeleteMembershipRoleEvent($roleInfo['id'], $isSystem);

            return true;
        }

        return false;
    }

    /**
     * Get all user's membership connections
     *
     * @param integer $userId
     * @param boolean $fullInfo 
     * @return Zend\Db\ResultSet\ResultSet
     */
    public function getAllUserMembershipConnections($userId, $fullInfo = false)
    {
        $select = $this->select();
        $select->from(['a' => 'membership_level_connection'])
            ->columns([
                'id',
                'active',
                'expire_date',
                'expire_value'
            ]);

        if ($fullInfo) {
            $select->join(
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
            );
        }

        $select->where([
            'user_id' => $userId
        ])
        ->order('a.id');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;

        return $resultSet->initialize($statement->execute());
    }

    /**
     * Delete the membership connection
     *
     * @param integer $connectionId
     * @param boolean $isSystem
     * @return boolean|string
     */
    public function deleteMembershipConnection($connectionId, $isSystem = true)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('membership_level_connection')
                ->where([
                    'id' => $connectionId
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        if ($result->count()) {
            // fire the delete membership connection event
            MembershipEvent::fireDeleteMembershipConnectionEvent($connectionId, $isSystem);

            return true;
        }

        return false;
    }

    /**
     * Get all memberhip levels
     *
     * @param integer $roleId
     * @return array
     */
    public function getAllMembershipLevels($roleId)
    {
        $select = $this->select();
        $select->from('membership_level')
            ->columns([
                'id',
                'image'
            ])
            ->where([
                'role_id' => $roleId
            ]);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }

    /**
     * Get a user's membership connection from a queue
     *
     * @param integer $userId
     * @return array
     */
    public function getMembershipConnectionFromQueue($userId)
    {
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
                    'role_id',
                    'lifetime',
                    'expiration_notification'
                ]
            )
            ->join(
                ['c' => 'acl_role'],
                'c.id = b.role_id',
                [
                    'role_name' => 'name'
                ]
            )
            ->where([
                'a.user_id' => $userId,
                'a.active' => self::MEMBERSHIP_LEVEL_CONNECTION_NOT_ACTIVE
            ])
            ->order('a.id')
            ->limit(1);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $result = $resultSet->initialize($statement->execute());

        return $result->current();
    }

    /**
     * Activate the membership connection
     *
     * @param integer $connectionId
     * @return boolean
     */
    public function activateMembershipConnection($connectionId)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $time = time();
            $update = $this->update()
                ->table('membership_level_connection')
                ->set([
                    'active' => self::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE,
                    'expire_date' => new Expression('? + (expire_value * ?)', [$time, self::SECONDS_IN_DAY]),
                    'notify_date' => new Expression('? + (notify_value * ?)', [$time, self::SECONDS_IN_DAY])
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

        if ($result->count()) {
            // fire the activate membership connection event
            MembershipEvent::fireActivateMembershipConnectionEvent($connectionId);

            return true;
        }

        return false;
    }
}