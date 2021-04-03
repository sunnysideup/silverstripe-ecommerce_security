<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use SilverStripe\Forms\FieldList;
use Sunnysideup\Ecommerce\Forms\Fields\EcommerceCMSButtonField;
use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 */
class OrderStepSecurityCheck extends OrderStep implements OrderStepInterface
{
    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLogSecurityCheck::class;

    private static $table_name = 'OrderStepSecurityCheck';

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Security Check for Order',
        'Code' => 'SECURITY_CHECK',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1,
    ];

    private static $_my_order = null;

    public function getCMSFields()
    {
        return parent::getCMSFields();
    }

    /**
     *initStep:
     * makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
     * should be able to run this function many times to check if the step is ready.
     *
     * @see Order::doNextStatus
     *
     * @return bool - true if the current step is ready to be run...
     */
    public function initStep(Order $order): bool
    {
        $logCount = $this->RelevantLogEntries($order)->count();
        if ($logCount) {
            //do nothing
        } else {
            $className = $this->relevantLogEntryClassName;
            $object = $className::create();
            $object->OrderID = $order->ID;
            $object->write();
        }

        return true;
    }

    /**
     *doStep:
     * should only be able to run this function once
     * (init stops you from running it twice - in theory....)
     * runs the actual step.
     *
     * @see Order::doNextStatus
     *
     * @return bool - true if run correctly
     */
    public function doStep(Order $order): bool
    {
        $entry = $this->RelevantLogEntry($order);
        if ($entry) {
            return $entry->pass();
        }

        return false;
    }

    /**
     *nextStep:
     * returns the next step (after it checks if everything is in place for the next step to run...).
     *
     * @see Order::doNextStatus
     *
     * @return null|OrderStep (next step OrderStep object)
     */
    public function nextStep(Order $order)
    {
        if ($this->doStep($order)) {
            return parent::nextStep($order);
        }

        return null;
    }

    /**
     * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields.
     *
     * @return FieldList
     */
    public function addOrderStepFields(FieldList $fields, Order $order)
    {
        $fields = parent::addOrderStepFields($fields, $order);
        $title = _t('OrderStep.MUST_ACTION_SECURITY_CHECKS', ' ... To move this order to the next step you have to carry out a bunch of security checks.');
        $field = $order->getOrderStatusLogsTableFieldEditable(OrderStatusLogSecurityCheck::class, $title);
        $logEntry = $this->RelevantLogEntry($order);
        $link = '/admin/sales/Order/EditForm/field/Order/item/' . $order->ID . '/ItemEditForm/field/OrderStatusLogSecurityCheck/item/' . $logEntry->ID . '/edit';
        $button = EcommerceCMSButtonField::create(
            'OrderStatusLogSecurityCheck_Button',
            $link,
            'Open Security Checks'
        );
        $fields->addFieldsToTab('Root.Next', [$button, $field], 'ActionNextStepManually');

        return $fields;
    }

    /**
     * For some ordersteps this returns true...
     *
     * @return bool
     */
    protected function hasCustomerMessage()
    {
        return false;
    }

    /**
     * Explains the current order step.
     *
     * @return string
     */
    protected function myDescription()
    {
        return 'Make sure that the Order is safe to proceed';
    }
}
