<?php

namespace Membership\Controller;

use Zend\View\Model\ViewModel;
use Application\Controller\ApplicationAbstractAdministrationController;

class MembershipAdministrationController extends ApplicationAbstractAdministrationController
{
    /**
     * Model instance
     * @var Membership\Model\MembershipAdministration
     */
    protected $model;

    /**
     * Get model
     *
     * @return Membership\Model\MembershipAdministration
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipAdministration');
        }

        return $this->model;
    }

    /**
     * Settings
     */
    public function settingsAction()
    {
        return new ViewModel(array(
            'settings_form' => parent::settingsForm('membership', 'membership-administration', 'settings')
        ));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        // redirect to list action
        return $this->redirectTo('membership-administration', 'list');
    }

    /**
     * List of membership levels
     */
    public function listAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        $filters = [];

        // get a filter form
        $filterForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Membership\Form\MembershipFilter');

        $request = $this->getRequest();
        $filterForm->getForm()->setData($request->getQuery(), false);

        // check the filter form validation
        if ($filterForm->getForm()->isValid()) {
            $filters = $filterForm->getForm()->getData();
        }

        // get data
        $paginator = $this->getModel()->getMembershipLevels($this->
                getPage(), $this->getPerPage(), $this->getOrderBy(), $this->getOrderType(), $filters);

        return new ViewModel([
            'filter_form' => $filterForm->getForm(),
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }
}