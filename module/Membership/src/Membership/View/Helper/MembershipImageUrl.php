<?php

namespace Membership\View\Helper;


use Application\Service\Application as ApplicationService;
use Zend\View\Helper\AbstractHelper;
use Membership\Model\MembershipBase as MembershipBaseModel;

class MembershipImageUrl extends AbstractHelper
{
    /**
     * Membership image url
     *
     * @param sting $image
     * @return string
     */
    public function __invoke($image)
    {
        return ApplicationService::getResourcesUrl() . MembershipBaseModel::getImagesDir() . $image;
    }
}
