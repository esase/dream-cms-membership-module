<?php

namespace Membership\Controller;

use Application\Controller\ApplicationAbstractBaseController;
use User\Service\UserIdentity as UserIdentityService;
use Zend\View\Model\ViewModel;

class MembershipAjaxController extends ApplicationAbstractBaseController
{
    /**
     * ACL Model instance
     * @var \Acl\Model\AclBase
     */
    protected $aclModel;

    /**
     * Get ACL model
     *
     * @return \Acl\Model\AclBase
     */
    protected function getAclModel()
    {
        if (!$this->aclModel) {
            $this->aclModel = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Acl\Model\AclBase');
        }

        return $this->aclModel;
    }

    /**
     * Get ACL resources
     */
    public function ajaxGetAclResourcesAction()
    {
        $view = new ViewModel([
            'resources' => $this->getAclModel()->
                    getAllowedAclResources($this->getSlug(), UserIdentityService::getCurrentUserIdentity()['user_id'])
        ]);

        return $view;
    }
}