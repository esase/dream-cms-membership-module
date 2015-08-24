<?php

namespace Membership\View\Widget;

use Page\View\Widget\PageAbstractWidget;

abstract class MembershipAbstractWidget extends PageAbstractWidget
{
    /**
     * Model instance
     * @var \Membership\Model\MembershipWidget
     */
    protected $model;

    /**
     * Get model
     *
     * @return \Membership\Model\MembershipWidget
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipWidget');
        }
        return $this->model;
    }

    /**
     * Include js and css files
     *
     * @return void
     */
    public function includeJsCssFiles()
    {
        $this->getView()->layoutHeadScript()->
                appendFile($this->getView()->layoutAsset('membership.js', 'js', 'membership'));

        $this->getView()->layoutHeadLink()->
                appendStylesheet($this->getView()->layoutAsset('main.css', 'css', 'membership'));
    }
}