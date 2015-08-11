<?php

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
     * @var string
     */
    protected $formName = 'membership-acl-role';

    /**
     * List of ignored elements
     * @var array
     */
    protected $ignoredElements = ['image'];

    /**
     * Image
     * @var string
     */
    protected $image;

    /**
     * Form elements
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
            'category' => 'General info',
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
     * @return Membership\Form\MembershipAclRole
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