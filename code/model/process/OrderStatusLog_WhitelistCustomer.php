<?php


/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLog_WhitelistCustomer extends OrderStatusLog
{

    private static $minimum_days_before_considered = 90;

    private static $db = array(
        'Whitelist' => 'Boolean'
    );

    private static $has_one = array(
        'Member' => 'Member',
        'BasedOn' => 'OrderStatusLog_WhitelistCustomer'
    );

    private static $defaults = array(
        'InternalUseOnly' => true
    );

    private static $singular_name = 'Whitelist Customer Record';
    public function i18n_singular_name()
    {
        return self::$singular_name;
    }

    private static $plural_name = 'Whitelist Customer Records';
    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

    /**
     * is a customer whitelisted...?
     * @param  Member $member
     * @return boolean
     */
    public static function customer_is_whitelist($member)
    {
        OrderStatusLog_WhitelistCustomer::get()->filter(array(
            'WhiteList' => 1,
            'MemberID' => $member->ID
        ))->count() ? true : false;
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
     *
     *
     * @param  Member  $member  the member to check
     * @return boolean          returns true of the member has been whitelisted before
     */
    public static function member_is_whitelisted(Member $member)
    {
        return OrderStatusLog_WhitelistCustomer::get()
            ->filter(
                array(
                    'MemberID' => $member->ID,
                    'Whitelist' => 1
                )
            )->count() ? true : false;
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }

    function onAfterWrite()
    {
        parent::onAfterWrite();
        if($this->Whitelist) {
            if($member = $this->Member()) {
                if($member->exists()) {
                    $member->IsWhitelisted = true;
                    $member->write();
                }
            }
        }
    }

    public function checkcustomer()
    {
        $order = $this->Order();
        if($order && $order->exists()) {
            if($order->MemberID) {
                $this->MemberID = $order->MemberID;
                $this->Whitelist = false;
                $member = $order->Member();
                if($member && $member->exists()) {
                    //check if member has previouly been whitelisted
                    $previousOne = OrderStatusLog_WhitelistCustomer::get()
                        ->filter(
                            array(
                                'Whitelist' => 1,
                                'MemberID' => $member->ID
                            )
                        )
                        ->exclude(
                            array('OrderID' => $order->ID)
                        )->first();
                    if($previousOne) {
                        $this->Whitelist = true;
                        $this->BasedOnID = $previousOne->ID;
                    } else {
                        //member placed successful order, at least xxx days ago...
                        $daysAgo = $this->Config()->get('minimum_days_before_considered');
                        $previousOne = OrderStatusLog_WhitelistCustomer::get()
                            ->filter(
                                array(
                                    'MemberID' => $member->ID,
                                    'Created:LessThan' => date('Y-m-d', strtotime('-'.$daysAgo.' days')).' 00:00:00'
                                )
                            )
                            ->exclude(
                                array('OrderID' => $order->ID)
                            )->first();
                        if($previousOne) {
                            $this->Whitelist = true;
                            $this->BasedOnID = $previousOne->ID;

                        } else {
                            $previousOrders = Order::get()
                                ->filter(
                                    array(
                                        'MemberID' => $member->ID,
                                        'Created:LessThan' => date('Y-m-d', strtotime('-'.$daysAgo.' days')).' 00:00:00'
                                    )
                                )
                                ->exclude(
                                    array(
                                        'ID' => $order->ID,
                                        'CancelledByID:greaterThan' => 0
                                    )
                                )->count();
                            $this->Whitelist = false;
                        }
                    }
                }
                $this->write();
            }
        }
    }



}
