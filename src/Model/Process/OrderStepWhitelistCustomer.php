<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use Override;
use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Process\OrderStepWhitelistCustomer
 *
 */
class OrderStepWhitelistCustomer extends OrderStep implements OrderStepInterface
{
    private static $table_name = 'OrderStepWhitelistCustomer';

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLogWhitelistCustomer::class;

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
     * @var bool
     */
    private $_completed;

    #[Override]
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
     * @return bool - true if the current step is ready to be run...
     */
    #[Override]
    public function initStep(Order $order): bool
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
     * @return bool - true if run correctly
     */
    #[Override]
    public function doStep(Order $order): bool
    {
        $log = $this->RelevantLogEntry($order);
        if (! $log) {
            $className = $this->relevantLogEntryClassName;
            $log = $className::create();
            $log->OrderID = $order->ID;
            $log->write();
        }

        $log->assessCustomer();

        return true;
    }

    #[Override]
    public function HideFromEveryone(): bool
    {
        return true;
    }

    /**
     * For some ordersteps this returns true...
     *
     * @return bool
     */
    #[Override]
    public function hasCustomerMessage()
    {
        return false;
    }

    /**
     * Explains the current order step.
     *
     * @return string
     */
    #[Override]
    protected function myDescription()
    {
        return 'Whitelist a customer if they qualify for this.';
    }
}
