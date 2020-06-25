<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;





use SilverStripe\Security\Member;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_WhitelistCustomer;
use Sunnysideup\CmsEditLinkField\Forms\Fields\CMSEditLinkField;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;




/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class OrderStatusLog_WhitelistCustomer extends OrderStatusLog
{
    /**
     * @var int
     */
    private static $min_number_of_paid_orders_required = 1;


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'OrderStatusLog_WhitelistCustomer';

    private static $db = array(
        'Whitelist' => 'Boolean'
    );

    private static $has_one = array(
        'Member' => Member::class,
        'BasedOn' => OrderStatusLog_WhitelistCustomer::class
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

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null, $context = [])
    {
        return parent::canEdit($member);
    }

    public function canDelete($member = null, $context = [])
    {
        return false;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->replaceField(
            'BasedOnID',

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: LinkField (case sensitive)
  * NEW: LinkField (COMPLEX)
  * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            CMSEditLinkField::create(
                'BasedOnID',
                _t('OrderStatusLog_WhitelistCustomer.BASED_ON', 'Based on'),
                $this->BasedOn()
            )
        );
        $fields->replaceField(
            'MemberID',

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: LinkField (case sensitive)
  * NEW: LinkField (COMPLEX)
  * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            CMSEditLinkField::create(
                'MemberID',
                _t('OrderStatusLog_WhitelistCustomer.CUSTOMER', 'Customer'),
                $this->Member()
            )
        );
        return $fields;
    }

    /**
     *
     *
     * @param  Member  $member  the member to check
     * @return boolean          returns true of the member is a security risk
     */
    public static function member_is_security_risk(Member $member)
    {
        return $member->IsSecurityRisk;
    }

    /**
     *
     *
     * @param  Member  $member  the member to check
     * @return boolean          returns true of the member has been whitelisted before
     */
    public static function member_is_whitelisted(Member $member)
    {
        if ($member->IsSecurityRisk) {
            return false;
        }
        return $member->IsWhitelisted;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->Whitelist) {
            if ($member = $this->Member()) {
                if ($member->exists()) {
                    $member->IsWhitelisted = true;
                    $member->write();
                }
            }
        }
    }

    public function assessCustomer()
    {
        //already done ...
        if ($this->Whitelist) {
            return true;
        }
        $order = $this->Order();
        if ($order && $order->exists()) {
            if ($order->MemberID) {
                $this->MemberID = $order->MemberID;
                $this->Whitelist = false;
                $member = $order->Member();
                if ($member && $member->exists()) {
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
                    if ($previousOne) {
                        $this->Whitelist = true;
                        $this->BasedOnID = $previousOne->ID;
                    } else {
                        //member has placed orders before
                        $previousOrders = Order::get()
                            ->filter(
                                array(
                                    'MemberID' => $member->ID,
                                    'CancelledByID' => 0
                                )
                            )
                            ->exclude(
                                array(
                                    'ID' => $order->ID
                                )
                            );
                        $count = 0;
                        $minOrdersRequired = $this->Config()->get('min_number_of_paid_orders_required');
                        foreach ($previousOrders as $previousOrder) {
                            if ($previousOrder->IsPaid() && $previousOrder->IsArchived()) {
                                $count++;
                                if ($count >= $minOrdersRequired) {
                                    $this->Whitelist = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                $this->write();
            }
        }
    }
}

