<?php

namespace Membership\Controller;

use Application\Controller\ApplicationAbstractBaseConsoleController;
use Acl\Model\AclBase as AclBaseModel;
use Localization\Service\Localization as LocalizationService;
use Application\Utility\ApplicationEmailNotification;
use Application\Service\ApplicationServiceLocator as ApplicationServiceLocatorService;

class MembershipConsoleController extends ApplicationAbstractBaseConsoleController
{
    /**
     * Items limit
     */
    const ITEMS_LIMIT = 500;

    /**
     * Model instance
     * @var Membership\Model\MembershipConsole
     */
    protected $model;

    /**
     * User Model instance
     * @var User\Model\UserBase  
     */
    protected $userModel;

    /**
     * Get models
     *
     * @return Membership\Model\MembershipConsole
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipConsole');
        }

        return $this->model;
    }

    /**
     * Get user model
     *
     * @return User\Model\UserBase
     */
    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('User\Model\UserBase');
        }

        return $this->userModel;
    }

    /**
     * Clean expired memberships connections
     */
    public function cleanExpiredMembershipsConnectionsAction()
    {
        $request = $this->getRequest();

        $deletedConnections  = 0;
        $notifiedConnections = 0;

        // get a list of expired memberships connections
        if (null != ($expiredConnections = $this->
                getModel()->getExpiredMembershipsConnections(self::ITEMS_LIMIT))) {

            // process expired memberships connections
            foreach ($expiredConnections as $connectionInfo) {
                // delete the connection
                if (false === ($deleteResult = 
                        $this->getModel()->deleteMembershipConnection($connectionInfo['id']))) {

                    break;
                }

                // get a next membership connection
                $nextConnection = $this->getModel()->getMembershipConnectionFromQueue($connectionInfo['user_id']);
                $nextRoleId = $nextConnection
                    ? $nextConnection['role_id']
                    : AclBaseModel::DEFAULT_ROLE_MEMBER;

                $nextRoleName = $nextConnection
                    ? $nextConnection['role_name']
                    : AclBaseModel::DEFAULT_ROLE_MEMBER_NAME;

                // change the user's role 
                if (true === ($result = $this->getUserModel()->
                        editUserRole($connectionInfo['user_id'], $nextRoleId, $nextRoleName, $connectionInfo, true))) {

                    // activate the next membership connection
                    if ($nextConnection) {
                        $this->getModel()->activateMembershipConnection($nextConnection['id']);
                    }
                }

                $deletedConnections++;
            }
        }

        // get list of not notified memberships connections
        if ((int) $this->applicationSetting('membership_expiring_send')) {
            if (null != ($notNotifiedConnections = $this->
                    getModel()->getNotNotifiedMembershipsConnections(self::ITEMS_LIMIT))) {

                // process not notified memberships connections
                foreach ($notNotifiedConnections as $connectionInfo) {
                    if (false === ($markResult = 
                            $this->getModel()->markConnectionAsNotified($connectionInfo['id']))) {

                        break;
                    }

                    // send a notification about membership expiring
                    $notificationLanguage = $connectionInfo['language']
                        ? $connectionInfo['language'] // we should use the user's language
                        : LocalizationService::getDefaultLocalization()['language'];

                    $locale = LocalizationService::getLocalizations()[$notificationLanguage]['locale'];

                    ApplicationEmailNotification::sendNotification($connectionInfo['email'],
                            $this->applicationSetting('membership_expiring_send_title', $notificationLanguage),
                            $this->applicationSetting('membership_expiring_send_message', $notificationLanguage), [
                                'find' => [
                                    'RealName',
                                    'Role',
                                    'ExpireDate'
                                ],
                                'replace' => [
                                    $connectionInfo['nick_name'],
                                    ApplicationServiceLocatorService::getServiceLocator()->
                                            get('Translator')->translate($connectionInfo['role_name'], 'default', $locale),

                                    ApplicationServiceLocatorService::getServiceLocator()->
                                            get('viewhelpermanager')->get('applicationDate')->__invoke($connectionInfo['expire_date'], [], $locale)
                                ]
                            ]);

                    $notifiedConnections++;
                }
            }
        }

        $verbose = $request->getParam('verbose');

        if (!$verbose) {
            return 'All expired  membership connections have been deleted.' . "\n";
        }

        $result  = $deletedConnections  . ' membership connections  have been deleted.'. "\n";
        $result .= $notifiedConnections . ' membership connections  have been notified.'. "\n";

        return $result;
    }
}