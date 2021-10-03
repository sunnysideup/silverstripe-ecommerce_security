<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLogSecurityCheck;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStepSecurityCheck;

class EcommerceSecurityOrderDecoration extends DataExtension
{
    private static $db = [
        'SkipToSecurityChecks' => 'Boolean',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->IsSubmitted()) {
            $currentStep = $this->owner->MyStep()->Sort;
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
                    ],
                    'ActionNextStepManually'
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
        if ($this->owner->SkipToSecurityChecks) {
            $logCount = OrderStatusLogSecurityCheck::get()->filter(['OrderID' => $this->owner->ID])->count();
            if ($logCount) {
                //do nothing - the security check already exists
            } else {
                $securityCheck = OrderStatusLogSecurityCheck::create();
                $securityCheck->OrderID = $this->owner->ID;
                $securityCheck->write();
                $securityStepID = OrderStep::get()->filter(['ClassName' => OrderStepSecurityCheck::class])->first()->ID;
                if ($securityStepID) {
                    $this->owner->StatusID = $securityStepID;
                }
            }
        }
    }
}
