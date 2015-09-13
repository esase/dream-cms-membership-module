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
namespace Membership\View\Widget;

use Membership\Model\MembershipBase as MembershipBaseModel;

class MembershipLevelWidget extends MembershipAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // get pagination params
        $page = $this->getRouteParam('page', 1);
        $perPage = $this->getRouteParam('per_page');
        $orderBy = $this->getRouteParam('order_by', 'cost');
        $orderType = $this->getRouteParam('order_type', 'asc');

        // paginator's filters
        $filters = array(
            'active' => MembershipBaseModel::MEMBERSHIP_LEVEL_STATUS_ACTIVE
        );

        // get list of active membership levels
        $paginator = $this->getModel()->getMembershipLevels($page, $perPage, $orderBy, $orderType, $filters);
        $wrapperId = 'memberships-wrapper';

        // get data list
        $dataList = $this->getView()->partial('partial/data-list', [
            'filter_form' => false,
            'ajax' => [
                'wrapper_id' => $wrapperId,
                'widget_connection' => $this->widgetConnectionId,
                'widget_position' => $this->widgetPosition
            ],
            'paginator' => $paginator,
            'paginator_order_list_show' => (int) $this->getWidgetSetting('membership_sorting_menu_membership_levels'),
            'paginator_order_list' => [
                'cost' => 'Cost',
                'title' => 'Title'
            ],
            'paginator_per_page_show' => (int) $this->getWidgetSetting('membership_per_page_menu_membership_levels'),
            'unit' => 'membership/partial/_membership-unit',
            'unit_params' => [
                'items_width_medium' => $this->getWidgetSetting('membership_list_item_width_medium'),
                'items_width_small' => $this->getWidgetSetting('membership_list_item_width_small'),
                'items_width_extra_small' => $this->getWidgetSetting('membership_list_item_width_extra_small')
            ],
            'uniform_height' => '#' . $wrapperId .' .membership-info',
            'per_page' => $perPage,
            'order_by' => $orderBy,
            'order_type' => $orderType
        ]);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $dataList;
        }

        return $this->getView()->partial('membership/widget/membership-level', [
            'membership_wrapper' => $wrapperId,
            'data' => $dataList
        ]);
    }
}