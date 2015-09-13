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
use Acl\Model\AclBase as AclBaseModel;
use Application\Service\Application as ApplicationService;
use Membership\Model\MembershipBase as  MembershipBaseModel;

class MembershipAclRole extends ApplicationAbstractCustomForm
{
    /**
     * Title max string length
     */
    const TITLE_MAX_LENGTH = 50;

    /**
     * Cost max string length
     */
    const COST_MAX_LENGTH = 11;

    /**
     * Lifetime string length
     */
    const LIFETIME_MAX_LENGTH = 4;

    /**
     * Expiration notification string length
     */
    const EXPIRATION_NOTIFICATION_MAX_LENGTH = 4;

    /**
     * Description string length
     */
    const DESCRIPTION_MAX_LENGTH = 65535;

    /**
     * Form name
     *
     * @var string
     */
    protected $formName = 'membership-acl-role';

    /**
     * List of ignored elements
     *
     * @var array
     */
    protected $ignoredElements = ['image'];

    /**
     * Image
     *
     * @var string
     */
    protected $image;

    /**
     * Form elements
     *
     * @var array
     */
    protected $formElements = [
        'title' => [
            'name' => 'title',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Title',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::TITLE_MAX_LENGTH
        ],
        'role_id' => [
            'name' => 'role_id',
            'type' => ApplicationCustomFormBuilder::FIELD_SELECT,
            'label' => 'Role',
            'required' => true,
            'category' => 'General info'
        ],
        'cost' => [
            'name' => 'cost',
            'type' => ApplicationCustomFormBuilder::FIELD_FLOAT,
            'label' => 'Cost',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::COST_MAX_LENGTH
        ],
        'lifetime' => [
            'name' => 'lifetime',
            'type' => ApplicationCustomFormBuilder::FIELD_INTEGER,
            'label' => 'Lifetime in days',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::LIFETIME_MAX_LENGTH
        ],
        'expiration_notification' => [
            'name' => 'expiration_notification',
            'type' => ApplicationCustomFormBuilder::FIELD_INTEGER,
            'label' => 'Expiration notification reminder in days',
            'description' => 'You can remind  users about the expiration after N days after the beginning',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::EXPIRATION_NOTIFICATION_MAX_LENGTH
        ],
        'description' => [
            'name' => 'description',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Description',
            'required' => true,
            'category' => 'General info',
            'max_length' => self::DESCRIPTION_MAX_LENGTH
        ],
        'image' => [
            'name' => 'image',
            'type' => ApplicationCustomFormBuilder::FIELD_IMAGE,
            'label' => 'Image',
            'required' => true,
            'extra_options' => [
                'file_url' => null,
                'preview' => false,
                'delete_option' => false
            ],
            'category' => 'General info'
        ],
        'active' => [
            'name' => 'active',
            'type' => ApplicationCustomFormBuilder::FIELD_CHECKBOX,
            'label' => 'Active',
            'required' => false,
            'category' => 'General info'
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Submit'
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

            $this->formElements['role_id']['values'] = $aclRoles;

            // add preview for the image
            if ($this->image) {
                $this->formElements['image']['required'] = false;
                $this->formElements['image']['extra_options']['preview'] = true;
                $this->formElements['image']['extra_options']['file_url'] =
                    ApplicationService::getResourcesUrl() . MembershipBaseModel::getImagesDir() . $this->image;
            }

            // add extra validators
            $this->formElements['expiration_notification']['validators'] = [
                [
                    'name' => 'callback',
                    'options' => [
                        'callback' => [$this, 'validateExpirationNotification'],
                        'message' => 'The expiration notification value  must be less than role\'s lifetime'
                    ]
                ]
            ];

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }

    /**
     * Set an image
     *
     * @param string $image
     * @return \Membership\Form\MembershipAclRole
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Validate the expiration notification
     *
     * @param $value
     * @param array $context
     * @return boolean
     */
    public function validateExpirationNotification($value, array $context = [])
    {
        return (int) $value < $context['lifetime'];
    }
}