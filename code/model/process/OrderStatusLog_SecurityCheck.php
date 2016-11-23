<?php


/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLog_SecurityCheck extends OrderStatusLog
{
    private static $days_ago_to_check = 14;

    private static $db = array(
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
    );

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
    private static $checks_required = array();

    private static $summary_fields = array(
        'Type' => 'Type',
        'SubTotal' => 'SubTotal',
        'SecurityCleared' => 'Security Cleared'
    );

    private static $casting = array(
        'SecurityCleared' => 'Boolean'
    );

    private static $defaults = array(
        'InternalUseOnly' => true
    );

    private static $singular_name = 'Security Check';
    public function i18n_singular_name()
    {
        return self::$singular_name;
    }

    private static $plural_name = 'Security Checks';
    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
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
            $member = $order->Member();
            $fields = parent::getCMSFields();
            if ($member) {
                $previousOrders = Order::get()
                    ->filter(
                        array(
                            'MemberID' => $member->ID
                        )
                    )
                    ->exclude(
                        array('ID' => $order->ID)
                    );
                if ($previousOrders->count()) {
                    $fields->addFieldToTab(
                        "Root.PreviousOrders",
                        new GridField(
                            'PreviousOrdersList',
                            'Previous Orders',
                            $previousOrders
                        )
                    );
                } else {
                    $fields->addFieldToTab(
                        "Root.PreviousOrders",
                        HeaderField::create(
                            'NoPreviousOrders',
                            'This customer does not have any previous orders'
                        )
                    );
                }
            }
        }
        $allFields = array();
        for ($i = 1; $i < 13; $i++) {
            $allFields["Check".$i] = "Check".$i;
        }
        $checks = $this->Config()->get('checks_required');
        $fields->addFieldToTab(
            "Root.Required",
            HeaderField::create('RequiredChecksHeader', 'Required Checks'),
            'Note'
        );
        $fields->addFieldToTab(
            "Root.NotRequired",
            HeaderField::create('UnrequiredChecksHeader', 'Optional Checks'),
            'Note'
        );
        $hasRequiredChecks = false;
        $hasUnrequiredChecks = false;
        $memberIsWhiteListed = $this->memberIsWhiteListed();
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
            if ($memberIsWhiteListed) {
                //..
            } else {
                unset($originalOptions['Whitelisted Customer']);
            }
            if (! $this->$fieldName) {
                $this->$fieldName = 'To do';
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
        if (! $hasUnrequiredChecks || 1 == 1) {
            $fields->addFieldToTab(
                "Root.NotRequired",
                HeaderField::create('UnrequiredChecksHeader', 'There are no optional checks for this order.'),
                'Note'
            );
        }
        if (! $hasRequiredChecks) {
            $fields->addFieldToTab(
                "Root.Required",
                HeaderField::create('RequiredChecksHeader', 'There are no required checks for this order.'),
                'Note'
            );
        }
        $fields->removeFieldFromTab('Root.Main', 'AuthorID');
        $fields->removeFieldFromTab('Root.Main', 'Title');
        $fields->removeFieldFromTab('Root.Main', 'InternalUseOnly');
        $fields->makeFieldReadonly('Risks');
        $fields->makeFieldReadonly('SubTotal');
        return $fields;
    }

    protected function collateRisks()
    {
        $order = $this->Order();
        $billingAddress = $order->BillingAddress();
        $shippingAddress = $order->ShippingAddress();
        $member = $order->Member();
        $payments = $order->Payments();
        $html = '';

        $similarArray = array();
        $warningMessages = array();

        $daysAgo = $this->Config()->get('days_ago_to_check');
        $timeFilter = array('Created:GreaterThan' => date('Y-m-d', strtotime('-'.$daysAgo.' days')).' 00:00:00');

        //check emails from user
        $emailArray = array();
        if ($member) {
            if ($member->Email) {
                $emailArray[] = $member->Email;
                if (OrderStatusLog_WhitelistCustomer::member_is_whitelisted($member)) {
                    $html .= '<h1 style="background-color: green; color: white; font-size: 20px; padding: 5px;">This customer is whitelisted</h1>';
                } else {
                    $html .= '<h1>This customer is NOT whitelisted</h1>';
                }
            }
        }
        //are there any orders with the same email in the last seven days...
        $otherOrders = Order::get()->filter(
            array('MemberID' => $member->ID) + $timeFilter
        )->exclude(array('ID' => $order->ID));
        foreach ($otherOrders as $otherOrder) {
            if (!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
        }
        //check emails from billing address
        $emailArray = array();
        if ($billingAddress) {
            if ($billingAddress->Email) {
                $emailArray[] = $billingAddress->Email;
            }
        }
        foreach ($emailArray as $email) {
            if ($email) {
                $obj = EcommerceSecurityEmail::find_or_create(array("Title" => $email));
                if ($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>EMAIL:</strong> $email<li>";
                }
            }
        }
        //are there any orders with the same email in the xxx seven days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            array('Email' => $emailArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if (!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
        }

        //phones
        $phoneArray = array();
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
            array('Phone' => $phoneArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID != $order->ID) {
                if (!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Phone"] = $otherOrder;
            }
        }
        $otherShippingAddresses = ShippingAddress::get()->filter(
            array('ShippingPhone' => $phoneArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach ($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID != $order->ID) {
                if (!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Phone"] = $otherOrder;
            }
        }

        //addresses
        $addressArray = array();
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
            array('Address' => $addressArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach ($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID != $order->ID) {
                if (!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Address"] = $otherOrder;
            }
        }
        $otherShippingAddresses = ShippingAddress::get()
            ->filter(
                array('ShippingAddress' => $addressArray) + $timeFilter
            )
            ->exclude(array('OrderID' => $order->ID));
        foreach ($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if ($otherOrder && $otherOrder->ID != $order->ID) {
                if (!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Address"] = $otherOrder;
            }
        }


        //IP
        $ipArray = array();
        $ipProxyArray = array();
        if ($payments) {
            foreach ($payments as $payment) {
                $ipArray[] = $payment->IP;
                $ipProxyArray[] = $payment->ProxyIP;
            }
        }
        //are there any orders with the same IP in the xxx seven days...
        foreach ($ipArray as $ip) {
            if ($ip) {
                $obj = EcommerceSecurityIP::find_or_create(array("Title" => $ip));
                if ($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>IP:</strong> $ip<li>";
                }
            }
        }
        foreach ($ipProxyArray as $proxyIP) {
            if ($proxyIP) {
                $obj = EcommerceSecurityProxyIP::find_or_create(array("Title" => $proxyIP));
                if ($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>PROXY IP:</strong> $proxyIP<li>";
                }
            }
        }
        $otherPayments = EcommercePayment::get()->filter(
            array('IP' => $ipArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach ($otherPayments as $payment) {
            $otherOrder = $payment->Order();
            if (!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["IP"] = $otherOrder;
        }
        if (count($warningMessages)) {
            $html .= '<h2 style="color: red;">Blacklisted Details</h2><ul class="SecurityCheckListOfRisks warnings" style="color: red;">';
            foreach ($warningMessages as $warningMessage) {
                $html .= $warningMessage;
            }
            $html .= '</ul>';
        } else {
            $html .= '<h2>No Blacklisted Data Present</h2>';
        }
        if (count($similarArray)) {
            $days = $this->Config()->get('days_ago_to_check');
            $html .= '<h2>Similar orders in the last '.$days.' days</h2><ul class="SecurityCheckListOfRisks otherRisks">';
            foreach ($similarArray as $orderID => $fields) {
                //we just loop this so we can get the order ...
                foreach ($fields as $tempOrder) {
                    break;
                }
                $html .= '<li><a href="'.$tempOrder->CMSEditLink().'">'.$tempOrder->getTitle().'</a>: with same '.implode(', and with same ', array_keys($fields)).'</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p class="message good">There were no similar orders in the last '.$daysAgo.' days</p>';
        }

        return $html;
    }

    public function getSecurityCleared()
    {
        return  DBField::create_field('Boolean', ($this->pass() ? true : false));
    }

    /**
     *
     *
     *
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
                if ($this->$fieldName == 'Done' || $this->$fieldName == 'Whitelisted Customer') {
                } else {
                    return false;
                }
            }
        }
        return true;
    }


    private static $_saved_already = false;

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (! self::$_saved_already) {
            self::$_saved_already = true;
            $order = $this->Order();
            if ($order && $order->exists()) {
                $this->SubTotal = $order->getSubTotal();
                $this->Risks = $this->collateRisks();
                $this->write();
            } else {
                $this->SubTotal = 9999;
                $this->Risks = "Error";
                $this->write();
            }
        }
    }

    protected function checkSecurityObject($obj)
    {
        if ($obj->Status == 'Bad') {
            return false;
        }
    }

    /**
     * caching variable only...
     *
     * @var null | bool
     */
    private $_memberIsWhiteListed = null;

    protected function memberIsWhiteListed()
    {
        if ($this->_memberIsWhiteListed  === null) {
            $order = $this->Order();
            if ($order && $order->exists()) {
                $member = $order->Member();
                if ($member && $member->exists()) {
                    $this->_memberIsWhiteListed = OrderStatusLog_WhitelistCustomer::member_is_whitelisted($member);
                }
            }
        }
        return $this->_memberIsWhiteListed;
    }
}
