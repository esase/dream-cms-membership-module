<?php

namespace Membership;

use User\Event\UserEvent;
use User\Model\UserBase as UserBaseModel;
use Membership\Event\MembershipEvent;
use Acl\Event\AclEvent;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    /**
     * Init
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = MembershipEvent::getEventManager();

        // TODO - delete  related memberships while languages delete
        // TODO - move synchronize users membership levels in console

        // someone forced a user's role, and now we must clean all the user's membership queue +
        $eventManager->attach(UserEvent::EDIT_ROLE, function ($e) use ($moduleManager) {
            if ($e->getParam('user_id') != UserBaseModel::DEFAULT_SYSTEM_ID) {
                $this->deleteUserMembershipLevels($moduleManager, $e->getParam('object_id'));
            }
        });

        // listen the delete acl role event
        $eventManager->attach(AclEvent::DELETE_ROLE, function ($e) use ($moduleManager) {
            $this->deleteMembershipLevels($moduleManager, $e->getParam('object_id'));
        });

        // listen the delete acl event
      /*  $eventManager->attach(AclEvent::DELETE_ROLE, function ($e) use ($moduleManager) {
            $modelManager = $moduleManager->getEvent()
                ->getParam('ServiceManager')
                ->get('Application\Model\ModelManager');

            $model = $modelManager->getInstance('Membership\Model\MembershipBase');

            // delete membership levels
            if (null != ($membershipLevels = 
                    $model->getAllMembershipLevels($e->getParam('object_id')))) {

                foreach ($membershipLevels as $levelInfo) {
                    $model->deleteRole($levelInfo, true);
                }
            }
        });*/

        /*$eventManager->attach(AclEvent::DELETE_ROLE, function ($e) use ($moduleManager) {
            // get the model manager instance
            $modelManager = $moduleManager->getEvent()
                ->getParam('ServiceManager')
                ->get('Application\Model\ModelManager');

            $model = $modelManager->getInstance('Membership\Model\MembershipBase');

            // delete connected membership levels
            if (null != ($membershipLevels = $model->getAllMembershipLevels($e->getParam('object_id')))) {
            
            
                foreach ($membershipLevels as $levelInfo) {
                    $model->deleteRole($levelInfo, true);
                }

                // synchronize users membership levels
                if (null != ($membershipLevels  = $model->getUsersMembershipLevels())) {
                    $userModel = $modelManager->getInstance('User\Model\Base');

                    // process membership levels
                    foreach ($membershipLevels as $levelInfo) {
                        // set the next membership level
                        if ($levelInfo['active'] != BaseMembershipModel::MEMBERSHIP_LEVEL_CONNECTION_ACTIVE) {
                            // change the user's role 
                            $userModel->editUserRole($levelInfo['user_id'], 
                                    $levelInfo['role_id'], $levelInfo['role_name'], $levelInfo, true);

                            // activate the next membership connection
                            $model->activateMembershipConnection($levelInfo['connection_id']);
                        }
                    }
                }
                
                
            }
        });*/
    }

    /**
     * Delete membership levels
     *
     * @param ModuleManagerInterface $moduleManager
     * @param integer $roleId
     * @return void
     */
    protected function deleteMembershipLevels(ModuleManagerInterface $moduleManager, $roleId)
    {
        $model = $moduleManager->getEvent()
            ->getParam('ServiceManager')
            ->get('Application\Model\ModelManager')
            ->getInstance('Membership\Model\MembershipBase');

        // delete membership levels
        if (null != ($membershipLevels = $model->getAllMembershipLevels($roleId))) {
            foreach ($membershipLevels as $levelInfo) {
                $model->deleteRole($levelInfo, true);
            }
        }
    }

    /**
     * Delete user's membership levels
     *
     * @param ModuleManagerInterface $moduleManager
     * @param integer $userId
     * @return void
     */
    protected function deleteUserMembershipLevels(ModuleManagerInterface $moduleManager, $userId)
    {
        $model = $moduleManager->getEvent()
            ->getParam('ServiceManager')
            ->get('Application\Model\ModelManager')
            ->getInstance('Membership\Model\MembershipBase');

        // delete all user's connections
        if (null != ($connections = $model->getAllUserMembershipConnections($userId))) {
            foreach ($connections as $connection) {
                // delete the connection
                if (false === ($deleteResult = $model->deleteMembershipConnection($connection->id))) {
                    break;
                }
            }
        }
    }

    /**
     * Return autoloader config array
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php'
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ]
            ]
        ];
    }

    /**
     * Return service config array
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
        ];
    }

    /**
     * Init view helpers
     */
    public function getViewHelperConfig()
    {
        return [
        ];
    }

    /**
     * Return path to config file
     *
     * @return boolean
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get console usage info
     *
     * @param object $console
     * @return array
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            // describe available commands
            'membership clean expired connections [--verbose|-v]' => 'Clean expired membership connections',
            // describe expected parameters
            ['--verbose|-v', '(optional) turn on verbose mode']
        ];
    }
}