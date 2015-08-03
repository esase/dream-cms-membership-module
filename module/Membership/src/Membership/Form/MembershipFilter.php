<?php

namespace Membership\Form;

use Application\Form\ApplicationCustomFormBuilder;
use Application\Form\ApplicationAbstractCustomForm;
use Acl\Service\Acl as AclService;
use Membership\Model\MembershipBase as MembershipBaseModel;
use Acl\Model\AclBase as AclBaseModel;

class MembershipFilter extends ApplicationAbstractCustomForm
{
    /**
     * Form name
     * @var string
     */
    protected $formName = 'filter';

    /**
     * Form method
     * @var string
     */
    protected $method = 'get';

    /**
     * List of not validated elements
     * @var array
     */
    protected $notValidatedElements = ['submit'];

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'title' => [
            'name' => 'title',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Title'
        ],
        'cost' => [
            'name' => 'cost',
            'type' => ApplicationCustomFormBuilder::FIELD_FLOAT,
            'label' => 'Cost'
        ],
        'lifetime' => [
            'name' => 'lifetime',
            'type' => ApplicationCustomFormBuilder::FIELD_INTEGER,
            'label' => 'Lifetime in days'
        ],
        'active' => [
            'name' => 'active',
            'type' => ApplicationCustomFormBuilder::FIELD_SELECT,
            'label' => 'Status',
            'values' => [
               MembershipBaseModel::MEMBERSHIP_LEVEL_STATUS_ACTIVE => 'approved',
               MembershipBaseModel::MEMBERSHIP_LEVEL_STATUS_NOT_ACTIVE => 'disapproved'
            ]
        ],
        'role' => [
            'name' => 'role',
            'type' => ApplicationCustomFormBuilder::FIELD_SELECT,
            'label' => 'Role'
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Search'
        ]
    ];

    /**
     * Get form instance
     *
     * @return Application\Form\ApplicationCustomFormBuilder
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            // get list of all ACL roles
            $aclRoles = [];
            foreach (AclService::getAclRoles() as $roleId => $roleName) {
                // skip all system ACL roles
                if (in_array($roleId, [AclBaseModel::DEFAULT_ROLE_ADMIN,
                    AclBaseModel::DEFAULT_ROLE_GUEST, AclBaseModel::DEFAULT_ROLE_MEMBER])) {

                    continue;
                }

                $aclRoles[$roleId] = $roleName;
            }

            // get list of acl roles
            $this->formElements['role']['values'] = $aclRoles;
            $this->form = new ApplicationCustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }
}