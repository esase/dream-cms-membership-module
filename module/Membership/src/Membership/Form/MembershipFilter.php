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
     *
     * @var string
     */
    protected $formName = 'filter';

    /**
     * Form method
     *
     * @var string
     */
    protected $method = 'get';

    /**
     * List of not validated elements
     *
     * @var array
     */
    protected $notValidatedElements = ['submit'];

    /**
     * Form elements
     *
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
     * @return \Application\Form\ApplicationCustomFormBuilder
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