<?php

namespace Membership;

use Localization\Event\LocalizationEvent;
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
     *
     * @param ModuleManagerInterface $moduleManager
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = MembershipEvent::getEventManager();

        $eventManager->attach(LocalizationEvent::UNINSTALL, function ($e) use ($moduleManager) {
            $this->deleteLanguageMembershipLevels($moduleManager, $e->getParam('object_id'));
        });

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
    }

    /**
     * Delete language membership levels
     *
     * @param ModuleManagerInterface $moduleManager
     * @param string $language
     * @return void
     */
    protected function deleteLanguageMembershipLevels(ModuleManagerInterface $moduleManager, $language)
    {
        $model = $moduleManager->getEvent()
            ->getParam('ServiceManager')
            ->get('Application\Model\ModelManager')
            ->getInstance('Membership\Model\MembershipBase');

        // delete membership levels
        if (null != ($membershipLevels = $model->getAllMembershipLevelsByLanguage($language))) {
            foreach ($membershipLevels as $levelInfo) {
                $model->deleteRole($levelInfo, true);
            }
        }
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
            'invokables' => [
                'membershipLevelWidget' => 'Membership\View\Widget\MembershipLevelWidget',
                'membershipImageUrl' => 'Membership\View\Helper\MembershipImageUrl'
            ],
            'factories' => [
            ]
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