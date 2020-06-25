<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

class OrderStep_WhitelistCustomer extends OrderStep implements OrderStepInterface
{
    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLog_WhitelistCustomer::class;

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Whitelist Customer',
        'Code' => 'WHITELIST_CUSTOMER',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1,
    ];

    /**
     * @var bool|null
     */
    private $_completed = null;

    public function getCMSFields()
    {
        return parent::getCMSFields();
    }

    /**
     * initStep:
     * makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
     * should be able to run this function many times to check if the step is ready.
     *
     * @see Order::doNextStatus
     *
     * @param Order $order object
     *
     * @return bool - true if the current step is ready to be run...
     **/
    public function initStep(Order $order)
    {
        return true;
    }

    /**
     * doStep:
     * should only be able to run this function once
     * (init stops you from running it twice - in theory....)
     * runs the actual step.
     *
     * @see Order::doNextStatus
     *
     * @param Order $order object
     *
     * @return bool - true if run correctly.
     **/
    public function doStep(Order $order)
    {
        if ($this->_completed !== null) {
            return $this->_completed;
        }
        $entry = $this->RelevantLogEntry($order);
        if (! $entry) {
            $className = $this->relevantLogEntryClassName;
            $entry = $className::create();
            $entry->OrderID = $order->ID;
            $entry->write();
        }
        $entry->assessCustomer();
        $this->_completed = true;

        return $this->_completed;
    }

    /**
     *nextStep:
     * returns the next step (after it checks if everything is in place for the next step to run...).
     *
     * @see Order::doNextStatus
     *
     * @param Order $order
     *
     * @return OrderStep | Null (next step OrderStep object)
     **/
    public function nextStep(Order $order)
    {
        if ($this->doStep($order)) {
            return parent::nextStep($order);
        }

        return;
    }

    public function HideFromEveryone()
    {
        return true;
    }

    /**
     * For some ordersteps this returns true...
     *
     * @return bool
     **/
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
        return 'Whitelist a customer if they qualify for this.';
    }
}
