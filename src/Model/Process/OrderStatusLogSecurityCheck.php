<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use GeoIP;












use SilverStripe\Control\Email\Email;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBField;
use Sunnysideup\Ecommerce\Model\Address\BillingAddress;
use Sunnysideup\Ecommerce\Model\Address\ShippingAddress;
use Sunnysideup\Ecommerce\Model\Money\EcommercePayment;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
use Sunnysideup\EcommerceSecurity\Interfaces\EcommerceSecurityLogInterface;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityAddress;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityBaseClass;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityIP;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityPhone;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityProxyIP;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLogSecurityCheck extends OrderStatusLog
{
    protected $warningMessages = [];

    private static $days_ago_to_check = 14;

    private static $table_name = 'OrderStatusLogSecurityCheck';

    private static $db = [
        'Bad' => 'Boolean',
        'Risks' => 'HTMLText',
        'SubTotal' => 'Currency',
        'Check1' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check2' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check3' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check4' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check5' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check6' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check7' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check8' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check9' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check10' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check11' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
        'Check12' => 'Enum("To do, Done, Whitelisted Customer", "To do" )',
    ];

    private static $many_many = [
        'BlacklistItems' => EcommerceSecurityBaseClass::class,
    ];

    /**
     *  this array works as follows
     *      array(
     *          Check1 => array(
     *              "Title" => "Customer Has Paid",
     *              "MinSubTotal" => 10,
     *              "Explanation" => "Check Payment system for $$$ coming in"
     *          ),
     *          Check2 => array(
     *              "Title" => "Address Exists",
     *              "MinSubTotal" => 50,
     *              "Explanation" => "Check Payment system for $$$ coming in"
     *          )
     *
     * @var array
     */
    private static $checks_required = [];

    private static $summary_fields = [
        'Type' => 'Type',
        'SubTotal' => 'SubTotal',
        'SecurityCleared' => 'Security Cleared',
    ];

    private static $casting = [
        'SecurityCleared' => 'Boolean',
    ];

    private static $defaults = [
        'InternalUseOnly' => true,
    ];

    private static $field_labels = [
        'Bad' => 'Fraudulent',
        'Title' => 'Value',
    ];

    private static $singular_name = 'Security Check';

    private static $plural_name = 'Security Checks';

    private static $_saved_already = 0;

    /**
     * caching variable only...
     *
     * @var bool|null
     */
    private $_memberIsWhitelisted = null;

    public function i18n_singular_name()
    {
        return self::$singular_name;
    }

    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        $order = $this->Order();
        if ($order && $order->exists()) {
            $status = $order->MyStep();
            if ($status && $status->Code === 'SECURITY_CHECK') {
                return parent::canEdit($member);
            }
            return false;
        }
        return parent::canEdit($member);
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $order = $this->Order();
        if ($order) {
            $member = $this->orderMember();
            $fields = parent::getCMSFields();
            $securityIP = '';
            foreach ($this->BlacklistItems() as $item) {
                if (is_a($item, EcommerceSecurityIP::class)) {
                    $securityIP = $item->Title;
                    break;
                }
            }
            if ($securityIP) {
                $country = '';
                if (class_exists('GeoIP')) {
                    $country = GeoIP::ip2country($securityIP)['name'];
                }
                $fields->addFieldToTab(
                    'Root.Main',
                    HeaderField::create(
                        'BadHeading',
                        'IP Address Info: '
                    ),
                    'Bad'
                );
                if ($country) {
                    $country = '<em>Country:</em> ' . $country . '</br>';
                }
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'IPAddressLink',
                        $country . '<em>Detailed Info: </em><a href="https://freegeoip.net/?q=' . $securityIP . '" target="_blank">https://freegeoip.net/?q=' . $securityIP . '</a>'
                    ),
                    'Bad'
                );
            }

            $fields->addFieldToTab(
                'Root.Main',
                HeaderField::create(
                    'BadHeading',
                    'Mark as Fraud'
                ),
                'Bad'
            );

            $fields->addFieldToTab(
                'Root.MoreDetails',
                HTMLEditorField::create(
                    'Note',
                    'Notes'
                )
            );
            $fields->addFieldToTab(
                'Root.Main',
                HeaderField::create(
                    'BadHeading',
                    'Risks'
                ),
                'Risks'
            );
            $riskField = $fields->dataFieldByName('Risks');
            $riskField->setTitle('');

            if ($member) {
                $previousOrders = Order::get()
                    ->filter(
                        [
                            'MemberID' => $member->ID,
                        ]
                    )
                    ->exclude(
                        ['ID' => $order->ID]
                    );
                if ($previousOrders->count()) {
                    $fields->addFieldToTab(
                        'Root.PreviousOrders',
                        new GridField(
                            'PreviousOrdersList',
                            'Previous Orders',
                            $previousOrders
                        )
                    );
                } else {
                    $fields->addFieldToTab(
                        'Root.PreviousOrders',
                        HeaderField::create(
                            'NoPreviousOrders',
                            'This customer does not have any previous orders'
                        )
                    );
                }
            }
        }
        $allFields = [];
        for ($i = 1; $i < 13; $i++) {
            $allFields['Check' . $i] = 'Check' . $i;
        }
        $checks = $this->Config()->get('checks_required');
        $fields->addFieldToTab(
            'Root.Required',
            HeaderField::create('RequiredChecksHeader', 'Required Checks'),
            'Note'
        );
        $fields->addFieldToTab(
            'Root.NotRequired',
            HeaderField::create('UnrequiredChecksHeader', 'Optional Checks'),
            'Note'
        );
        $hasRequiredChecks = false;
        $hasUnrequiredChecks = false;
        $memberIsWhitelisted = $this->memberIsWhitelisted();
        foreach ($checks as $fieldName => $details) {
            unset($allFields[$fieldName]);
            if (floatval($this->SubTotal) > floatval($details['SubTotalMin'])) {
                $hasRequiredChecks = true;
                $fields->addFieldToTab(
                    'Root.Required',
                    $myField = $fields->dataFieldByName($fieldName)
                );
            } else {
                $hasUnrequiredChecks = true;
                $fields->addFieldToTab(
                    'Root.NotRequired',
                    $myField = $fields->dataFieldByName($fieldName)
                );
            }
            $originalOptions = $myField->getSource();
            if ($memberIsWhitelisted) {
                //..
            } else {
                unset($originalOptions['Whitelisted Customer']);
            }
            if (! $this->{$fieldName}) {
                $this->{$fieldName} = 'To do';
            }
            $fields->replaceField(
                $myField->ID(),
                OptionsetField::create(
                    $myField->ID(),
                    $details['Title'],
                    $originalOptions
                )
            );
            if (! empty($details['Description'])) {
                $myField->setRighTitle($details['Description']);
            }
        }
        foreach ($allFields as $fieldToRemove) {
            $fields->removeByName($fieldToRemove);
        }
        if (! $hasUnrequiredChecks) {
            $fields->addFieldToTab(
                'Root.NotRequired',
                HeaderField::create('UnrequiredChecksHeader', 'There are no optional checks for this order.'),
                'Note'
            );
        }
        if (! $hasRequiredChecks) {
            $fields->addFieldToTab(
                'Root.Required',
                HeaderField::create('RequiredChecksHeader', 'There are no required checks for this order.'),
                'Note'
            );
        }
        if ($order) {
            $implementers = ClassInfo::implementorsOf(EcommerceSecurityLogInterface::class);
            if ($implementers) {
                foreach ($implementers as $implementer) {
                    $class = Injector::inst()->get($implementer);
                    $fields->addFieldsToTab(
                        'Root.Main',
                        [
                            $class->getSecurityHeader(),
                            $class->getSecuritySummary($order),
                        ]
                    );

                    if ($class->getSecurityLogTable($order)) {
                        $fields->addFieldsToTab(
                            'Root.' . $class->getSecurityLogTableTabName(),
                            [
                                $class->getSecurityHeader(),
                                $class->getSecurityLogTable($order),
                            ]
                        );
                    }
                }
            }
        }

        $fields->removeFieldFromTab('Root.Main', 'AuthorID');
        $fields->removeFieldFromTab('Root.Main', 'Title');
        $fields->removeFieldFromTab('Root.Main', 'InternalUseOnly');
        $fields->makeFieldReadonly('Risks');
        $fields->makeFieldReadonly('SubTotal');
        return $fields;
    }

    public function getSecurityCleared()
    {
        return DBField::create_field(DBBoolean::class, ($this->pass() ? true : false));
    }

    /**
     * @param  Order $order
     * @return bool
     */
    public function pass()
    {
        $order = $this->Order();
        if (! $order) {
            return false;
        }
        $checks = $this->Config()->get('checks_required');
        $fieldsAvailable = $this->stat('db');
        foreach ($checks as $fieldName => $fieldDetails) {
            if (floatval($this->SubTotal) > floatval($fieldDetails['SubTotalMin'])) {
                if (! isset($fieldsAvailable[$fieldName])) {
                    user_error('bad field  ....');
                }
                // there is a check that needs to be TRUE, but is not ...
                if ($this->{$fieldName} === 'Done' || $this->{$fieldName} === 'Whitelisted Customer') {
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $order = $this->Order();
        if (self::$_saved_already < 3) {
            self::$_saved_already++;
            if ($order && $order->exists()) {
                $this->SubTotal = $order->getSubTotal();
                $this->Risks = $this->collateRisks();
                $this->write();
            } else {
                $this->SubTotal = 9999;
                $this->Risks = 'Error';
                $this->write();
            }
            if ($this->memberIsWhitelisted()) {
                for ($i = 1; $i < 13; $i++) {
                    $field = 'Check' . $i;
                    $this->{$field} = 'Whitelisted Customer';
                }
                $this->write();
            }
        }
        if ($this->Bad) {
            foreach ($this->BlacklistItems() as $blacklistItem) {
                $blacklistItem->Status = 'Bad';
                $blacklistItem->write();
            }
            if ($order && $order->exists()) {
                $order->Archive(true);
            }
            if ($member = $this->orderMember()) {
                $member->IsWhitelisted = false;
                $member->IsSecurityRisk = true;
                $member->write();
            }
        }
    }

    protected function collateRisks()
    {
        $order = $this->Order();
        $billingAddress = $order->BillingAddress();
        $shippingAddress = $order->ShippingAddress();
        $member = $this->orderMember();
        $payments = $order->Payments();
        $html = '';

        $similarArray = [];

        $daysAgo = $this->Config()->get('days_ago_to_check');
        $timeFilter = ['Created:GreaterThan' => date('Y-m-d', strtotime('-' . $daysAgo . ' days')) . ' 00:00:00'];

        //check emails from user
        $emailArray = [];
        if ($member) {
            if ($member->Email) {
                $emailArray[] = $member->Email;
                if (OrderStatusLogWhitelistCustomer::member_is_security_risk($member)) {
                    $html .= '<p class="message bad">This customer has been marked as a security risk.</p>';
                } else {
                    if (OrderStatusLogWhitelistCustomer::member_is_whitelisted($member)) {
                        $html .= '<p class="warning good">This customer is whitelisted.</p>';
                    } else {
                        $html .= '<p class="message warning">This customer is NOT whitelisted.</p>';
                    }
                }
            }
        }
        //are there any orders with the same Member.email in the last seven days...
        $otherOrders = Order::get_datalist_of_orders_with_submit_record()
            ->filter(
                array_merge(
                    ['MemberID' => $member->ID],
                    $timeFilter
                )
            )
            ->exclude(['ID' => $order->ID]);
        foreach ($otherOrders as $otherOrder) {
            if (! isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = [];
            }
            $similarArray[$otherOrder->ID][Email::class] = $otherOrder;
        }
        //check emails from billing address
        $emailArray = [];
        if ($billingAddress) {
            if ($billingAddress->Email) {
                $emailArray[] = $billingAddress->Email;
            }
        }
        //are there any orders with the same Billing.Email in the last seven days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            ['Email' => $emailArray] + $timeFilter
        )->exclude(['OrderID' => $order->ID]);
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if (! isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = [];
            }
            $similarArray[$otherOrder->ID][Email::class] = $otherOrder;
        }
        //adding all emails to security checks
        $this->blacklistCheck($emailArray, EcommerceSecurityEmail::class);

        //phones
        $phoneArray = [];
        if ($billingAddress) {
            if ($billingAddress->Phone) {
                $phoneArray[] = $billingAddress->Phone;
            }
        }
        if ($shippingAddress) {
            if ($shippingAddress->ShippingPhone) {
                $phoneArray[] = $shippingAddress->ShippingPhone;
            }
        }
        //are there any orders with the same phone in the last xxx days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            ['Phone' => $phoneArray] + $timeFilter
        )->exclude(['OrderID' => $order->ID]);
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID !== $order->ID) {
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['Phone'] = $otherOrder;
            }
        }
        $otherShippingAddresses = ShippingAddress::get()->filter(
            ['ShippingPhone' => $phoneArray] + $timeFilter
        )->exclude(['OrderID' => $order->ID]);
        foreach ($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID !== $order->ID) {
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['Phone'] = $otherOrder;
            }
        }
        //adding all emails to security checks
        $this->blacklistCheck($phoneArray, EcommerceSecurityPhone::class);

        //addresses
        $addressArray = [];
        if ($billingAddress) {
            if ($billingAddress->Address) {
                $addressArray[] = $billingAddress->Address;
            }
        }
        if ($shippingAddress) {
            if ($shippingAddress->ShippingAddress) {
                $addressArray[] = $shippingAddress->ShippingAddress;
            }
        }
        //are there any orders with the same address in the last xxx days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            ['Address' => $addressArray] + $timeFilter
        )->exclude(['OrderID' => $order->ID]);
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID !== $order->ID) {
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['Address'] = $otherOrder;
            }
        }
        $otherShippingAddresses = ShippingAddress::get()
            ->filter(
                ['ShippingAddress' => $addressArray] + $timeFilter
            )
            ->exclude(['OrderID' => $order->ID]);
        foreach ($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID !== $order->ID) {
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['Address'] = $otherOrder;
            }
        }
        $this->blacklistCheck($addressArray, EcommerceSecurityAddress::class);

        //IP
        $ipArray = [];
        $ipProxyArray = [];
        if ($payments) {
            foreach ($payments as $payment) {
                if (strlen($payment->IP) > 10) {
                    $ipArray[] = $payment->IP;
                }
                if (strlen($payment->ProxyIP) > 10) {
                    $ipProxyArray[] = $payment->ProxyIP;
                }
            }
        }
        if (count($ipArray)) {
            //are there any orders with the same IP in the xxx seven days...
            $otherPayments = EcommercePayment::get()->filter(
                ['IP' => $ipArray] + $timeFilter
            )->exclude(['OrderID' => $order->ID]);
            foreach ($otherPayments as $payment) {
                $otherOrder = $payment->Order();
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['IP'] = $otherOrder;
            }
            $this->blacklistCheck($ipArray, EcommerceSecurityIP::class);
        }
        if (count($ipProxyArray)) {
            //are there any orders with the same Proxy in the xxx seven days...
            $otherPayments = EcommercePayment::get()->filter(
                ['ProxyIP' => $ipProxyArray] + $timeFilter
            )->exclude(['OrderID' => $order->ID]);
            foreach ($otherPayments as $payment) {
                $otherOrder = $payment->Order();
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID]['ProxyIP'] = $otherOrder;
            }
            $this->blacklistCheck($ipProxyArray, EcommerceSecurityProxyIP::class);
        }

        if (count($this->warningMessages)) {
            $html .= '
                <h4 style="color: red;">Blacklisted Details</h4>
                <ul class="SecurityCheckListOfRisks warnings" style="color: red;">';
            foreach ($this->warningMessages as $warningMessage) {
                $html .= $warningMessage;
            }
            $html .= '
                </ul>';
        } else {
            $html .= '<p class="message good">No Blacklisted Data Present</p>';
        }
        if (count($similarArray)) {
            $days = $this->Config()->get('days_ago_to_check');
            $html .= '
                <h4>Similar orders in the last ' . $days . ' days</h4>
                <ul class="SecurityCheckListOfRisks otherRisks">';
            foreach ($similarArray as $orderID => $fields) {
                //we just loop this so we can get the order ...
                foreach ($fields as $tempOrder) {
                    break;
                }
                $html .= '
                    <li><a href="' . $tempOrder->CMSEditLink() . '">' . $tempOrder->getTitle() . '</a>: with same ' . implode(', and with same ', array_keys($fields)) . '</li>';
            }
            $html .= '
                </ul>';
        } else {
            $html .= '<p class="message good">No similar orders in the last ' . $daysAgo . ' days</p>';
        }

        return $html;
    }

    /**
     * @param  EcommerceSecurityBaseClass $obj
     * @return bool                             return true if the status of the object is `Bad`
     */
    protected function checkSecurityObject($obj)
    {
        if ($obj->Status === 'Bad') {
            return false;
        }
        return true;
    }

    protected function memberIsWhitelisted()
    {
        if ($this->_memberIsWhitelisted === null) {
            if ($member = $this->orderMember()) {
                $this->_memberIsWhitelisted = OrderStatusLogWhitelistCustomer::member_is_whitelisted($member);
            }
        }
        return $this->_memberIsWhitelisted;
    }

    /**
     * @return Member|null
     */
    protected function orderMember()
    {
        $order = $this->Order();
        if ($order && $order->exists()) {
            $member = $order->Member();
            if ($member && $member->exists()) {
                return $member;
            }
        }
    }

    protected function blacklistCheck($arrayOfValues, $securityClass)
    {
        //adding all emails to security checks
        foreach ($arrayOfValues as $value) {
            if ($value) {
                $obj = $securityClass::find_or_create(['Title' => $value]);
                if ($obj->exists()) {
                    $this->BlacklistItems()->add($obj);
                    if ($obj->hasRisks()) {
                        $title = $obj->i18n_singular_name();
                        $message = '<li><strong>' . $title . ':</strong> ' . $value . '<li>';
                        $this->warningMessages[$message] = $message;
                    }
                }
            }
        }
    }
}
