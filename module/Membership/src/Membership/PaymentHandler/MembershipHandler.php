<?php

namespace Membership\PaymentHandler;

use Payment\Handler\PaymentAbstractHandler;

class MembershipHandler extends PaymentAbstractHandler 
{
    /**
     * Model instance
     * @var Membership\Model\MembershipBase
     */
    protected $model;

    /**
     * Get model
     *
     * @return Membership\Model\MembershipBase
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->serviceLocator
                ->get('Application\Model\ModelManager')
                ->getInstance('Membership\Model\MembershipBase');
        }

        return $this->model;
    }

    /**
     * Get the item's info
     *
     * @param integer $id
     * @return array
     *      string|integer slug - optional
     *      string title - required
     *      float|array cost - required
     *      float discount - optional
     *      integer count - required (only for countable modules)
     *      array extra_options - optional (a form array notation)
     */
    public function getItemInfo($id)
    {
        // get membership info
        if (null == ($roleInfo = $this->getModel()->getRoleInfo($id, true))) {
            return;
        }

        return [
            'slug' => null,
            'title' => $roleInfo['title'],
            'cost' => $roleInfo['cost'],
            'discount' => $this->getDiscount($id),
            'count' => 1
        ];
    }

    /**
     * Get the items' extra options
     *
     * @param integer $id
     * @return array
     */
    public function getItemExtraOptions($id)
    {}

    /**
     * Get discount
     *
     * @param integer $id
     * @return float
     */
    public function getDiscount($id)
    {}

    /**
     * Clear the discount
     *
     * @param integer $id
     * @param float $discount
     * @return void
     */
    public function clearDiscount($id, $discount)
    {}

    /**
     * Return back the discount
     *
     * @param integer $id
     * @param float $discount
     * @return void
     */
    public function returnBackDiscount($id, $discount)
    {}

    /**
     * Decrease the item's count 
     *
     * @param integer $id
     * @param integer $count
     * @return void
     */
    public function decreaseCount($id, $count)
    {}

    /**
     * Set the item as paid
     *
     * @param integer $id
     * @param array $transactionInfo
     *      integer id
     *      string slug
     *      integer user_id
     *      string first_name
     *      string last_name
     *      string phone
     *      string address
     *      string email
     *      integer currency
     *      integer payment_type
     *      integer discount_cupon
     *      string currency_code
     *      string payment_name 
     * @return void
     */
    public function setPaid($id, array $transactionInfo)
    {}
}