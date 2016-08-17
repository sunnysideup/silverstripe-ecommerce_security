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
        'Check1' => 'Boolean',
        'Check2' => 'Boolean',
        'Check3' => 'Boolean',
        'Check4' => 'Boolean',
        'Check5' => 'Boolean',
        'Check6' => 'Boolean',
        'Check7' => 'Boolean',
        'Check8' => 'Boolean',
        'Check9' => 'Boolean',
        'Check10' => 'Boolean',
        'Check11' => 'Boolean',
        'Check12' => 'Boolean',
    );

    /**
     *  this array works as follows
     *      array(
     *          Check1 => array(
     *              "Title" => "Customer Has Paid",
     *              "MinSubTotal" => 10
     *          ),
     *          Check2 => array(
     *              "Title" => "Address Exists",
     *              "MinSubTotal" => 50
     *          )
     *
     * @var array
     */
    private static $checks_required = array();

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

    function canCreate($member = null)
    {
        return false;
    }

    function canEdit($member = null)
    {
        return true;
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $allFields = array();
        for($i = 1; $i < 13; $i++) {
            $allFields["Check".$i] = "Check".$i;
        }
        $checks = $this->Config()->get('checks_required');
        $fields->addFieldToTab(
            "Root.Required",
            HeaderField::create('RequiredChecksHeader',''),
            'Note'
        );
        $fields->addFieldToTab(
            "Root.NotRequired",
            HeaderField::create('UnrequiredChecksHeader','Checks that are not required for this order'),
            'Note'
        );
        $hasRequiredChecks = false;
        $hasUnrequiredChecks = false;
        $memberIsWhiteListed = $this->memberIsWhiteListed();
        foreach($checks as $fieldName => $details) {
            unset($allFields[$fieldName]);
            if(floatval($this->SubTotal) > floatval($details['SubTotalMin'])) {
                $hasRequiredChecks = true;
                $fields->addFieldToTab(
                    'Root.Required',
                    $fields->dataFieldByName($fieldName)->setTitle($details['Title'])
                );
            } else {
                $hasUnrequiredChecks = true;
                $fields->addFieldToTab(
                    'Root.NotRequired',
                    $fields->dataFieldByName($fieldName)->setTitle($details['Title'])
                );
            }
        }
        foreach($allFields as $fieldToRemove) {
            $fields->removeByName($fieldToRemove);
        }
        if( ! $hasUnrequiredChecks) {
            $fields->removeFieldFromTab('Root.Main','UnrequiredChecksHeader');
        }
        if( ! $hasRequiredChecks) {
            $fields->removeFieldFromTab('Root.Main','RequiredChecksHeader');
        }
        $fields->removeFieldFromTab('Root.Main','AuthorID');
        $fields->removeFieldFromTab('Root.Main','Title');
        $fields->removeFieldFromTab('Root.Main','InternalUseOnly');
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
        if($member) {
            if($member->Email) {
                $emailArray[] = $member->Email;
                if(OrderStatusLog_WhitelistCustomer::member_is_whitelisted($member)) {
                    $html .= '<h1 style="background-color: green; color: white;">This customer is whitelisted</h1>';
                }
            }
        }
        //are there any orders with the same email in the last seven days...
        $otherOrders = Order::get()->filter(
            array('MemberID' => $member->ID) + $timeFilter
        )->exclude(array('ID' => $order->ID));
        foreach($otherOrders as $otherOrder) {
            if(!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
        }
        //check emails from billing address
        $emailArray = array();
        if($billingAddress) {
            if($billingAddress->Email) {
                $emailArray[] = $billingAddress->Email;
            }
        }
        foreach($emailArray as $email) {
            if($email) {
                $obj = EcommerceSecurityEmail::find_or_create(array("Title" => $email));
                if($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>EMAIL:</strong> $email<li>";
                }
            }
        }
        //are there any orders with the same email in the xxx seven days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            array('Email' => $emailArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if(!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
        }

        //phones
        $phoneArray = array();
        if($billingAddress) {
            if($billingAddress->Phone) {
                $phoneArray[] = $billingAddress->Phone;
            }
        }
        if($shippingAddress) {
            if($shippingAddress->ShippingPhone) {
                $phoneArray[] = $shippingAddress->ShippingPhone;
            }
        }
        //are there any orders with the same phone in the last xxx days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            array('Phone' => $phoneArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if($otherOrder && $otherOrder->ID != $order->ID) {
                if(!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Phone"] = $otherOrder;
            }
        }
        $otherShippingAddresses = ShippingAddress::get()->filter(
            array('ShippingPhone' => $phoneArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if($otherOrder && $otherOrder->ID != $order->ID) {
                if(!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Phone"] = $otherOrder;
            }
        }

        //addresses
        $addressArray = array();
        if($billingAddress) {
            if($billingAddress->Address) {
                $addressArray[] = $billingAddress->Address;
            }
        }
        if($shippingAddress) {
            if($shippingAddress->ShippingAddress) {
                $addressArray[] = $shippingAddress->ShippingAddress;
            }
        }
        //are there any orders with the same address in the last xxx days...
        $otherBillingAddresses = BillingAddress::get()->filter(
            array('Address' => $addressArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach($otherBillingAddresses as $address) {
            $otherOrder = $address->Order();
            if($otherOrder && $otherOrder->ID != $order->ID) {
                if(!isset($similarArray[$otherOrder->ID])) {
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
        foreach($otherShippingAddresses as $address) {
            $otherOrder = $address->Order();
            if($otherOrder && $otherOrder->ID != $order->ID) {
                if(!isset($similarArray[$otherOrder->ID])) {
                    $similarArray[$otherOrder->ID] = array();
                }
                $similarArray[$otherOrder->ID]["Address"] = $otherOrder;
            }
        }


        //IP
        $ipArray = array();
        $ipProxyArray = array();
        if($payments) {
            foreach($payments as $payment) {
                $ipArray[] = $payment->IP;
                $ipProxyArray[] = $payment->ProxyIP;
            }
        }
        //are there any orders with the same IP in the xxx seven days...
        foreach($ipArray as $ip) {
            if($ip) {
                $obj = EcommerceSecurityIP::find_or_create(array("Title" => $ip));
                if($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>IP:</strong> $ip<li>";
                }
            }
        }
        foreach($ipProxyArray as $proxyIP) {
            if($proxyIP) {
                $obj = EcommerceSecurityProxyIP::find_or_create(array("Title" => $proxyIP));
                if($obj->hasRisks()) {
                    $warningMessages[] = "<li><strong>PROXY IP:</strong> $proxyIP<li>";
                }
            }
        }
        $otherPayments = EcommercePayment::get()->filter(
            array('IP' => $ipArray) + $timeFilter
        )->exclude(array('OrderID' => $order->ID));
        foreach($otherPayments as $payment) {
            $otherOrder = $payment->Order();
            if(!isset($similarArray[$otherOrder->ID])) {
                $similarArray[$otherOrder->ID] = array();
            }
            $similarArray[$otherOrder->ID]["IP"] = $otherOrder;
        }
        if(count($warningMessages)) {
            $html .= '<h2>Blacklisted Details</h2><ul class="SecurityCheckListOfRisks warnings">';
            foreach($warningMessages as $warningMessage) {
                $html .= $warningMessage;
            }
            $html .= '</ul>';
        } else {
            $html .= '<h2>No Blacklisted Data Present</h2>';
        }
        if(count($similarArray)) {
            $days = $this->Config()->get('days_ago_to_check');
            $html .= '<h2>Similar orders in the last '.$days.' days</h2><ul class="SecurityCheckListOfRisks otherRisks">';
            foreach($similarArray as $orderID => $fields) {
                //we just loop this so we can get the order ...
                foreach($fields as $tempOrder) {
                    break;
                }
                $html .= '<li><a href="'.$tempOrder->CMSEditLink().'">'.$tempOrder->getTitle().'</a>: with same '.implode(', and with same ', array_keys($fields)).'</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p class="message good">There were no similar orders in the last '.$days.' days</p>';
        }

        return $html;
    }

    /**
     *
     *
     *
     * @param  Order $order
     * @return bool
     */
    function pass()
    {
        $order = $this->Order();
        if( ! $order) {
            return false;
        }
        $checks = $this->Config()->get('checks_required');
        $fieldsAvailable = $this->stat('db');
        foreach($checks as $fieldName => $fieldDetails) {
            if(floatval($this->SubTotal) > floatval($fieldDetails['SubTotalMin'])) {
                if(! isset($fieldsAvailable[$fieldName])) {
                    user_error('bad field  ....');
                }
                // there is a check that needs to be TRUE, but is not ...
                if( ! $this->$fieldName) {
                    return false;
                }
            }
        }
        return true;
    }


    private static $_saved_already = false;

    function onAfterWrite()
    {
        parent::onAfterWrite();
        if( ! self::$_saved_already) {
            self::$_saved_already = true;
            $order = $this->Order();
            if($order && $order->exists()) {
                $this->SubTotal = $order->getSubTotal();
                $this->Risks = $this->collateRisks();
                $this->write();
            }
            else {
                $this->SubTotal = 9999;
                $this->Risks = "Error";
                $this->write();
            }
        }
    }

    protected function checkSecurityObject($obj)
    {
        if($obj->Status == 'Bad') {
            return false;
        }
    }

    /**
     * caching variable only...
     *
     * @var null | bool
     */
    private $_memberIsWhiteListed = null;

    protected function memberIsWhiteListed() {
        if($this->_memberIsWhiteListed  === null) {
            $order = $this->Order();
            if($order && $order->exists()) {
                $member = $order->Member();
                if($member && $member->exists()) {
                    $this->_memberIsWhiteListed = OrderStatusLog_WhitelistCustomer::member_is_whitelisted($member);
                }
            }
        }
        return $this->_memberIsWhiteListed;
    }

}
