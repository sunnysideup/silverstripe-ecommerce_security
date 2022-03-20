<?php

namespace Sunnysideup\EcommerceSecurity\Model\Process;

use SilverStripe\Security\Member;
use Sunnysideup\CmsEditLinkField\Forms\Fields\CMSEditLinkField;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;

/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: model
 * @inspiration: Silverstripe Ltd, Jeremy
 */
class OrderStatusLogWhitelistCustomer extends OrderStatusLog
{
    /**
     * @var int
     */
    private static $min_number_of_paid_orders_required = 1;

    private static $table_name = 'OrderStatusLogWhitelistCustomer';

    private static $db = [
        'Whitelist' => 'Boolean',
    ];

    private static $has_one = [
        'Member' => Member::class,
        'BasedOn' => OrderStatusLogWhitelistCustomer::class,
    ];

    private static $defaults = [
        'InternalUseOnly' => true,
    ];

    private static $singular_name = 'Whitelist Customer Record';

    private static $plural_name = 'Whitelist Customer Records';

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
        return parent::canEdit($member);
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->replaceField(
            'BasedOnID',
            CMSEditLinkField::create(
                'BasedOnID',
                _t('OrderStatusLogWhitelistCustomer.BASED_ON', 'Based on'),
                $this->BasedOn()
            )
        );
        $fields->replaceField(
            'MemberID',
            CMSEditLinkField::create(
                'MemberID',
                _t('OrderStatusLogWhitelistCustomer.CUSTOMER', 'Customer'),
                $this->Member()
            )
        );

        return $fields;
    }

    /**
     * @param Member $member the member to check
     *
     * @return bool returns true of the member is a security risk
     */
    public static function member_is_security_risk(Member $member) : bool
    {
        return $member->IsSecurityRisk;
    }

    /**
     * @param Member $member the member to check
     *
     * @return bool returns true of the member has been whitelisted before
     */
    public static function member_is_whitelisted(Member $member): bool
    {
        if ($member->IsSecurityRisk) {
            return false;
        }

        return $member->IsWhitelisted ? true : false;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->Whitelist) {
            $member = $this->Member();
            if ($member) {
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
        $order = $this->getOrderCached();
        if ($order && $order->exists()) {
            if ($order->MemberID) {
                $this->MemberID = $order->MemberID;
                $this->Whitelist = false;
                $member = $order->Member();
                if ($member && $member->exists()) {
                    //check if member has previouly been whitelisted
                    $previousOne = OrderStatusLogWhitelistCustomer::get()
                        ->filter(
                            [
                                'Whitelist' => 1,
                                'MemberID' => $member->ID,
                            ]
                        )
                        ->exclude(
                            ['OrderID' => $order->ID]
                        )->first();
                    if ($previousOne) {
                        $this->Whitelist = true;
                        $this->BasedOnID = $previousOne->ID;
                    } else {
                        //member has placed orders before
                        $previousOrders = Order::get()
                            ->filter(
                                [
                                    'MemberID' => $member->ID,
                                    'CancelledByID' => 0,
                                ]
                            )
                            ->exclude(
                                [
                                    'ID' => $order->ID,
                                ]
                            )
                        ;
                        $count = 0;
                        $minOrdersRequired = $this->Config()->get('min_number_of_paid_orders_required');
                        foreach ($previousOrders as $previousOrder) {
                            if ($previousOrder->IsPaid() && $previousOrder->IsArchived()) {
                                ++$count;
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
