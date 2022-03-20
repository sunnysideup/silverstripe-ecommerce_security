<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

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

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use Sunnysideup\Ecommerce\Api\EcommerceCountryVisitorCountryProvider;
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

use Sunnysideup\EcommerceSecurity\Model\Process\OrderStepSecurityCheck;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 */
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
     * @var bool
     */
    private $_memberIsWhitelisted;

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        $order = $this->getOrderCached();
        if ($order && $order->exists()) {
            $step = $order->MyStep();
            if ($step && 'SECURITY_CHECK' === $step->Code) {
                return parent::canEdit($member);
            } else {
            }

            return false;
        }

        return parent::canEdit($member);
    }

    /**
     * CMS Fields.
     *
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $order = $this->getOrderCached();
        if ($order) {
            $member = $this->orderMember();
            $securityIP = '';
            foreach ($this->BlacklistItems() as $item) {
                if (is_a($item, EcommerceSecurityIP::class)) {
                    $securityIP = $item->Title;

                    break;
                }
            }
            if ($securityIP) {
                $country = EcommerceCountryVisitorCountryProvider::ip2country($securityIP);
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
                    )
                ;
                if ($previousOrders->exists()) {
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
            $checks = $this->ChecksList();
            $requiredChecks = $this->RequiredChecks($order);
            $allFields = [];
            for ($i = 1; $i < 13; ++$i) {
                $baseList['Check' . $i] = 'Check' . $i;
            }
            foreach ($checks as $fieldName => $details) {
                unset($baseList[$fieldName]);
                $tab = 'Main';
                if (isset($requiredChecks[$fieldName])) {
                    $hasRequiredChecks = true;
                } else {
                    $hasUnrequiredChecks = true;
                    $tab = 'NotRequired';
                }
                $fields->addFieldToTab(
                    'Root.'.$tab,
                    $myField = $fields->dataFieldByName($fieldName)
                );
                $originalOptions = $myField->getSource();
                if (!$memberIsWhitelisted) {
                    // can't set to whitelisted, as customer is not whitelisted
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
                    $myField->setRightTitle($details['Description']);
                }
            }
            foreach ($baseList as $fieldToRemove) {
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
     * @return bool
     */
    public function pass()
    {
        $order = $this->getOrderCached();
        if (! $order) {
            return false;
        }
        $checks = $this->RequiredChecks($order);
        $fieldsAvailable = $this->stat('db');
        foreach ($checks as $fieldName => $fieldDetails) {
            if (! isset($fieldsAvailable[$fieldName])) {
                user_error('bad field  ....'.$fieldName);
            }
            // there is a check that needs to be TRUE, but is not ...
            if (! ( 'Done' === $this->{$fieldName} || 'Whitelisted Customer' === $this->{$fieldName} ) ) {
                return false;
            }
        }

        return true;
    }

    public function ChecksList() : array
    {

        $obj = DataObject::get_one(OrderStepSecurityCheck::class);
        if($obj) {
            return $obj->ChecksList();
        }
        return [];
    }

    protected $_requiredChecks = [];

    public function RequiredChecks(Order $order) : array
    {
        if(empty($this->_requiredChecks)) {
            $list = $this->ChecksList();
            $i = 0;
            $isWhiteListed = $this->memberIsWhitelisted();
            $memberIsSecurityRisk = $this->memberIsSecurityRisk();
            foreach($list as $step => $details) {
                $i++;
                // too loo
                if ( floatval($this->SubTotal) < floatval($details['SubTotalMin'])) {
                    continue;
                }
                // by-pass whitelisted customers
                if($isWhiteListed && !empty($details['WhitelistedCustomersExempt'])) {
                    continue;
                }
                // not a security risk and ONLY apply to security risk customers
                if(!$memberIsSecurityRisk && !empty($details['OnlyApplyToSecurityRiskCustomers'])) {
                    continue;
                }
                $this->_requiredChecks[$step] = $details;
            }
        }
        return $this->_requiredChecks;

    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $order = $this->getOrderCached();
        if (self::$_saved_already < 3) {
            ++self::$_saved_already;
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
                for ($i = 1; $i < 13; ++$i) {
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
            $member = $this->orderMember();
            if ($member) {
                $member->IsWhitelisted = false;
                $member->IsSecurityRisk = true;
                $member->write();
            }
        }
    }

    protected $order = null;
    protected $timeFilter = '';
    protected $billingAddress = '';
    protected $shippingAddress = '';

    protected function collateRisks()
    {
        $this->order = $this->getOrderCached();
        $order = $this->order;
        $this->billingAddress = $this->order->BillingAddress();
        $this->shippingAddress = $this->order->ShippingAddress();
        $member = $this->orderMember();
        $payments = $this->order->Payments();
        $html = '';

        $similarArray = [];

        $daysAgo = $this->Config()->get('days_ago_to_check');
        $this->timeFilter = ['Created:GreaterThan' => date('Y-m-d', strtotime('-' . $daysAgo . ' days')) . ' 00:00:00'];

        //check emails from user
        if ($member) {
            if ($member->Email) {

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
                    $this->timeFilter
                )
            )
            ->exclude(['ID' => $order->ID])
        ;
        foreach ($otherOrders as $otherOrder) {
            if (! isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = [];
            }
            $similarArray[$otherOrder->ID]['Email'] = $otherOrder;
        }


        $emailArray = [];
        $emailArray[] = $billingAddress->Email ?? '';
        $emailArray[] = $member->Email ?? '';
        //adding all emails to security checks
        $this->blacklistCheck($emailArray, EcommerceSecurityEmail::class);

        $similarArray += $this->checkOrderAddress('Email', '', EcommerceSecurityEmail::class);
        $similarArray += $this->checkOrderAddress('Phone', 'ShippingPhone', EcommerceSecurityPhone::class);
        $similarArray += $this->checkOrderAddress('Address', 'ShippingAddress', EcommerceSecurityAddress::class);


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
        $similarArray += $this->ipCheck('IP',$ipArray, EcommerceSecurityIP::class);
        $similarArray += $this->ipCheck('ProxyIP',$ipProxyArray, EcommerceSecurityProxyIP::class);

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
            foreach ($similarArray as $fields) {
                $tempOrder = null;
                //we just loop this so we can get the order ...
                foreach ($fields as $tempOrder) {
                    break;
                }
                if ($tempOrder) {
                    $html .= '
                    <li><a href="' . $tempOrder->CMSEditLink() . '">' . $tempOrder->getTitle() . '</a>: with same ' . implode(', ', array_keys($fields)) . '</li>';
                }
            }
            $html .= '
                </ul>';
        } else {
            $html .= '<p class="message good">No similar orders in the last ' . $daysAgo . ' days</p>';
        }

        return $html;
    }

    protected function ipCheck(string $field, array $array, string $className) : array
    {
        $similarArray = [];
        if (count($array)) {
            //are there any orders with the same IP in the xxx seven days...
            $otherPayments = EcommercePayment::get()->filter(
                [$field => $array] + $this->timeFilter
            )->exclude(['OrderID' => $this->order->ID]);
            foreach ($otherPayments as $payment) {
                $otherOrder = $payment->getOrderCached();
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID][$field] = $otherOrder;
            }
            $this->blacklistCheck($array, $className);
        }
        return $similarArray;
    }

    protected function checkOrderAddress($billingField, $shippingField, $securityClass) : array
    {
        $similarArray = [];
        //phones
        $testArray = [];
        if ($this->billingAddress) {
            if ($this->billingAddress->$billingField) {
                $testArray[] = $this->billingAddress->$billingField;
            }
        }
        if($shippingField) {
            if ($this->shippingAddress) {
                if ($this->shippingAddress->$shippingField) {
                    $testArray[] = $this->shippingAddress->$shippingField;
                }
            }
        }
        //are there any orders with the same phone in the last xxx days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            [$billingField => $testArray] + $this->timeFilter
        )->exclude(['OrderID' => $this->order->ID]);
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->getOrderCached();
            if ($otherOrder && $otherOrder->ID !== $this->order->ID) {
                if (! isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = [];
                }
                $similarArray[$otherOrder->ID][$billingField] = $otherOrder;
            }
        }
        if($shippingField) {
            $otherShippingAddresses = ShippingAddress::get()->filter(
                [$shippingField => $testArray] + $this->timeFilter
            )->exclude(['OrderID' => $this->order->ID]);
            foreach ($otherShippingAddresses as $address) {
                $otherOrder = $address->getOrderCached();
                if ($otherOrder && $otherOrder->ID !== $this->order->ID) {
                    if (! isset($similarArray[$otherOrder->ID])) {
                        $similarArray[$otherOrder->ID] = [];
                    }
                    $similarArray[$otherOrder->ID][$billingField] = $otherOrder;
                }
            }
        }
        //adding all emails to security checks
        $this->blacklistCheck($testArray, $securityClass);
        return $similarArray;
    }

    protected function memberIsWhitelisted()
    {
        if (null === $this->_memberIsWhitelisted) {
            $member = $this->orderMember();
            if ($member) {
                $this->_memberIsWhitelisted = OrderStatusLogWhitelistCustomer::member_is_whitelisted($member);
            }
        }

        return $this->_memberIsWhitelisted;
    }

    protected function memberIsSecurityRisk()
    {
        if (null === $this->_memberIsWhitelisted) {
            $member = $this->orderMember();
            if ($member) {
                $this->_memberIsWhitelisted = OrderStatusLogWhitelistCustomer::member_is_security_risk($member);
            }
        }

        return $this->_memberIsWhitelisted;
    }

    protected $_orderMember = null;

    /**
     * @return null|\SilverStripe\Security\Member
     */
    protected function orderMember(): ?Member
    {
        if(! $this->_orderMember) {
            $order = $this->getOrderCached();
            if ($order && $order->exists()) {
                $member = $order->Member();
                if ($member && $member->exists()) {
                    $this->_orderMember = $member;
                }
            }
        }

        return $this->_orderMember;
    }

    /**
     * set warningMessages
     */
    protected function blacklistCheck(array $arrayOfValues, string $securityClass)
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
