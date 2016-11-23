<?php

class OrderStep_WhitelistCustomer extends OrderStep implements OrderStepInterface
{
    private static $defaults = array(
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Whitelist Customer',
        'Code' => 'WHITELIST_CUSTOMER',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1
    );

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = 'OrderStatusLog_WhitelistCustomer';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

    /**
     *initStep:
     * makes sure the step is ready to run.... (e.g. check if the order is ready to be emailed as receipt).
     * should be able to run this function many times to check if the step is ready.
     *
     * @see Order::doNextStatus
     *
     * @param Order object
     *
     * @return bool - true if the current step is ready to be run...
     **/
    public function initStep(Order $order)
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

    private static $_passed = null;

    /**
     *doStep:
     * should only be able to run this function once
     * (init stops you from running it twice - in theory....)
     * runs the actual step.
     *
     * @see Order::doNextStatus
     *
     * @param Order object
     *
     * @return bool - true if run correctly.
     **/
    public function doStep(Order $order)
    {
        if (self::$_passed !== null) {
            return self::$_passed;
        }
        if ($entry = $this->RelevantLogEntry($order)) {
            $entry->checkCustomer();
            self::$_passed = true;
            return self::$_passed;
        }
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
    
    public function HideFromEveryone()
    {
        return true;
    }
}
