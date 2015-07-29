<?php

namespace Membership\Model;

use Membership\Event\MembershipEvent;
use Application\Utility\ApplicationErrorLogger;
use Application\Model\ApplicationAbstractBase;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Db\Sql\Expression as Expression;
use Payment\Model\PaymentBase as PaymentBaseModel;
use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Membership\Exception\MembershipException;
use Exception;

class MembershipBase extends ApplicationAbstractBase
{
    /**
     * Membership level active status flag
     */
    const MEMBERSHIP_LEVEL_STATUS_ACTIVE = 1;

    /**
     * Membership level not active status flag
     */
    const MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE = 0;

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
     * @return array
     */
    public function getRoleInfo($id, $onlyActive = false)
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

        // fire the delete membership role event
        MembershipEvent::fireDeleteMembershipRoleEvent($roleInfo['id'], $isSystem);

        return $result->count() ? true : false;
    }
}