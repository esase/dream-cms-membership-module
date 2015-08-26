<?php

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