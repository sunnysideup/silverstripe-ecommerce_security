<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLogSecurityCheck;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStepSecurityCheck;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityOrderDecoration
 *
 * @property \Sunnysideup\Ecommerce\Model\Order|\Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityOrderDecoration $owner
 * @property bool $SkipToSecurityChecks
 */
class EcommerceSecurityOrderDecoration extends DataExtension
{
    private static $db = [
        'SkipToSecurityChecks' => 'Boolean',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->getOwner()->IsSubmitted()) {
            $currentStep = $this->getOwner()->MyStep()->Sort;
            $securityStep = OrderStep::get()->filter(['ClassName' => OrderStepSecurityCheck::class])->first()->Sort;
            if ($currentStep < $securityStep) {
                $fields->addFieldsToTab(
                    'Root.Process',
                    [
                        CheckboxField::create(
                            'SkipToSecurityChecks',
                            'Lets Skip To Security Checks'
                        )->setDescription(
                            'Ticking this checkbox will skip the payment step, allowing security checks to be conducted for orders that do not have successful payments.'
                        ),
                    ]
                );
            }
        }
    }

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->getOwner()->SkipToSecurityChecks) {
            $logExists = OrderStatusLogSecurityCheck::get()->filter(['OrderID' => $this->getOwner()->ID])->exists();
            if (! $logExists) {
                $securityCheck = OrderStatusLogSecurityCheck::create();
                $securityCheck->OrderID = $this->getOwner()->ID;
                $securityCheck->write();
                $securityStepID = OrderStep::get()->filter(['ClassName' => OrderStepSecurityCheck::class])->first()->ID;
                if ($securityStepID) {
                    $this->getOwner()->StatusID = $securityStepID;
                }
            }
        }
    }
}
