<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Process\OrderStepSecurityCheck
 *
 * @property string $Title1
 * @property string $CheckDescription1
 * @property int $CheckDescriptionMinAmount1
 * @property bool $WhitelistedCustomersExempt1
 * @property bool $OnlyApplyToSecurityRiskCustomers1
 * @property string $Title2
 * @property string $CheckDescription2
 * @property int $CheckDescriptionMinAmount2
 * @property bool $WhitelistedCustomersExempt2
 * @property bool $OnlyApplyToSecurityRiskCustomers2
 * @property string $Title3
 * @property string $CheckDescription3
 * @property int $CheckDescriptionMinAmount3
 * @property bool $WhitelistedCustomersExempt3
 * @property bool $OnlyApplyToSecurityRiskCustomers3
 * @property string $Title4
 * @property string $CheckDescription4
 * @property int $CheckDescriptionMinAmount4
 * @property bool $WhitelistedCustomersExempt4
 * @property bool $OnlyApplyToSecurityRiskCustomers4
 * @property string $Title5
 * @property string $CheckDescription5
 * @property int $CheckDescriptionMinAmount5
 * @property bool $WhitelistedCustomersExempt5
 * @property bool $OnlyApplyToSecurityRiskCustomers5
 * @property string $Title6
 * @property string $CheckDescription6
 * @property int $CheckDescriptionMinAmount6
 * @property bool $WhitelistedCustomersExempt6
 * @property bool $OnlyApplyToSecurityRiskCustomers6
 * @property string $Title7
 * @property string $CheckDescription7
 * @property int $CheckDescriptionMinAmount7
 * @property bool $WhitelistedCustomersExempt7
 * @property bool $OnlyApplyToSecurityRiskCustomers7
 * @property string $Title8
 * @property string $CheckDescription8
 * @property int $CheckDescriptionMinAmount8
 * @property bool $WhitelistedCustomersExempt8
 * @property bool $OnlyApplyToSecurityRiskCustomers8
 * @property string $Title9
 * @property string $CheckDescription9
 * @property int $CheckDescriptionMinAmount9
 * @property bool $WhitelistedCustomersExempt9
 * @property bool $OnlyApplyToSecurityRiskCustomers9
 * @property string $Title10
 * @property string $CheckDescription10
 * @property int $CheckDescriptionMinAmount10
 * @property bool $WhitelistedCustomersExempt10
 * @property bool $OnlyApplyToSecurityRiskCustomers10
 * @property string $Title11
 * @property string $CheckDescription11
 * @property int $CheckDescriptionMinAmount11
 * @property bool $WhitelistedCustomersExempt11
 * @property bool $OnlyApplyToSecurityRiskCustomers11
 * @property string $Title12
 * @property string $CheckDescription12
 * @property int $CheckDescriptionMinAmount12
 * @property bool $WhitelistedCustomersExempt12
 * @property bool $OnlyApplyToSecurityRiskCustomers12
 */
class OrderStepSecurityCheck extends OrderStep implements OrderStepInterface
{
    protected $_checkLists = [];

    /**
     * The OrderStatusLog that is relevant to the particular step.
     *
     * @var string
     */
    protected $relevantLogEntryClassName = OrderStatusLogSecurityCheck::class;

    /**
     *  this array works as follows
     *      array(
     *          Check1 => array(
     *              "Title" => "Customer Has Paid",
     *              "MinSubTotal" => 10,
     *              "WhitelistedCustomersExempt" => false,
     *              "OnlyApplyToSecurityRiskCustomers" => true,
     *              "Explanation" => "Check Payment system for $$$ coming in",
     *          ),
     *          Check2 => array(
     *              "Title" => "Address Exists",
     *              "MinSubTotal" => 50,
     *              "WhitelistedCustomersExempt" => false,
     *              "OnlyApplyToSecurityRiskCustomers" => true,
     *              "Explanation" => "Check Payment system for $$$ coming in"
     *          ).
     *
     * @var array
     */
    private static $checks_required = [];

    private static $table_name = 'OrderStepSecurityCheck';

    /**
     * ```php
     *     [
     *         'MethodToReturnTrue' => StepClassName
     *     ]
     * ```
     * MethodToReturnTrue must have an $order as a parameter and bool as the return value
     * e.g. MyMethod(Order $order) : bool;.
     *
     * @var array
     */
    private static $step_logic_conditions = [
        'PassSecurityCheck' => true,
    ];

    private static $db = [
        'Title1' => 'Varchar',
        'CheckDescription1' => 'Varchar',
        'CheckDescriptionMinAmount1' => 'Int',
        'WhitelistedCustomersExempt1' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers1' => 'Boolean',
        'Title2' => 'Varchar',
        'CheckDescription2' => 'Varchar',
        'CheckDescriptionMinAmount2' => 'Int',
        'WhitelistedCustomersExempt2' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers2' => 'Boolean',
        'Title3' => 'Varchar',
        'CheckDescription3' => 'Varchar',
        'CheckDescriptionMinAmount3' => 'Int',
        'WhitelistedCustomersExempt3' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers3' => 'Boolean',
        'Title4' => 'Varchar',
        'CheckDescription4' => 'Varchar',
        'CheckDescriptionMinAmount4' => 'Int',
        'WhitelistedCustomersExempt4' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers4' => 'Boolean',
        'Title5' => 'Varchar',
        'CheckDescription5' => 'Varchar',
        'CheckDescriptionMinAmount5' => 'Int',
        'WhitelistedCustomersExempt5' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers5' => 'Boolean',
        'Title6' => 'Varchar',
        'CheckDescription6' => 'Varchar',
        'CheckDescriptionMinAmount6' => 'Int',
        'WhitelistedCustomersExempt6' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers6' => 'Boolean',
        'Title7' => 'Varchar',
        'CheckDescription7' => 'Varchar',
        'CheckDescriptionMinAmount7' => 'Int',
        'WhitelistedCustomersExempt7' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers7' => 'Boolean',
        'Title8' => 'Varchar',
        'CheckDescription8' => 'Varchar',
        'CheckDescriptionMinAmount8' => 'Int',
        'WhitelistedCustomersExempt8' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers8' => 'Boolean',
        'Title9' => 'Varchar',
        'CheckDescription9' => 'Varchar',
        'CheckDescriptionMinAmount9' => 'Int',
        'WhitelistedCustomersExempt9' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers9' => 'Boolean',
        'Title10' => 'Varchar',
        'CheckDescription10' => 'Varchar',
        'CheckDescriptionMinAmount10' => 'Int',
        'WhitelistedCustomersExempt10' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers10' => 'Boolean',
        'Title11' => 'Varchar',
        'CheckDescription11' => 'Varchar',
        'CheckDescriptionMinAmount11' => 'Int',
        'WhitelistedCustomersExempt11' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers11' => 'Boolean',
        'Title12' => 'Varchar',
        'CheckDescription12' => 'Varchar',
        'CheckDescriptionMinAmount12' => 'Int',
        'WhitelistedCustomersExempt12' => 'Boolean',
        'OnlyApplyToSecurityRiskCustomers12' => 'Boolean',
    ];

    private static $defaults = [
        'CustomerCanEdit' => 0,
        'CustomerCanCancel' => 0,
        'CustomerCanPay' => 0,
        'Name' => 'Security Check for Order',
        'Code' => 'SECURITY_CHECK',
        'ShowAsInProcessOrder' => 1,
        'HideStepFromCustomer' => 1,
    ];

    private static $_my_order;

    public function ChecksList(): array
    {
        if (empty($this->_checkLists)) {
            for ($i = 1; $i < 13; ++$i) {
                $titleField = 'Title' . $i;
                $subTotalMinField = 'CheckDescriptionMinAmount' . $i;
                if ($this->{$titleField} && $this->{$subTotalMinField}) {
                    $descriptionField = 'CheckDescription' . $i;
                    $whitelistedCustomersExemptField = 'WhitelistedCustomersExempt' . $i;
                    $onlyApplyToSecurityRiskCustomersField = 'OnlyApplyToSecurityRiskCustomers' . $i;
                    $this->_checkLists['Check' . $i] = [
                        'Title' => (string) $this->{$titleField},
                        'Description' => (string) $this->{$descriptionField},
                        'SubTotalMin' => (int) $this->{$subTotalMinField},
                        'WhitelistedCustomersExempt' => (bool) $this->{$whitelistedCustomersExemptField},
                        'OnlyApplyToSecurityRiskCustomers' => (bool) $this->{$onlyApplyToSecurityRiskCustomersField},
                    ];
                }
            }
            if (empty($this->_checkLists)) {
                $this->_checkLists = $this->Config()->get('checks_required');
            }
        }

        return $this->_checkLists;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        for ($i = 1; $i < 13; ++$i) {
            $fields->addFieldsToTab(
                'Root.Checks',
                [
                    HeaderField::create('CheckDescriptionHeader' . $i, 'Check ' . $i),

                    TextField::create('Title' . $i, 'Name of check')
                        ->setDescription('e.g. call customer, check address, review items, etc...'),

                    TextField::create('CheckDescription' . $i, 'Description of check')
                        ->setDescription('e.g. calling the customer and ask them if they ordered, bla bla, etc...'),

                    NumericField::create('CheckDescriptionMinAmount' . $i, 'Minimum Order Sub-Total')
                        ->setDescription('Minimum amount for order; must be greater than zero.'),

                    CheckboxField::create(
                        'WhitelistedCustomersExempt' . $i,
                        'Whitelisted customers exempt'
                    ),

                    CheckboxField::create(
                        'OnlyApplyToSecurityRiskCustomers' . $i,
                        'Only apply to security risk customers'
                    ),
                ]
            );
        }

        return $fields;
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
        $logsExist = $this->RelevantLogEntries($order)->exists();
        if (! $logsExist) {
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
        return true;
    }

    public function PassSecurityCheck(Order $order): bool
    {
        $entry = $this->RelevantLogEntry($order);
        if ($entry) {
            return (bool) $entry->pass();
        }

        return false;
    }

    /**
     * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields.
     *
     * @return FieldList
     */
    public function addOrderStepFields(FieldList $fields, Order $order, ?bool $nothingToDo = false)
    {
        $fields = parent::addOrderStepFields($fields, $order);
        $title = _t('OrderStep.MUST_ACTION_SECURITY_CHECKS', ' ... To move this order to the next step you have to carry out a bunch of security checks.');
        $field = $order->getOrderStatusLogsTableFieldEditable(OrderStatusLogSecurityCheck::class, $title);
        $fields->addFieldsToTab(
            'Root.Next',
            [
                $field,
            ]
        );

        return $fields;
    }

    /**
     * For some ordersteps this returns true...
     *
     * @return bool
     */
    public function hasCustomerMessage()
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
