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
            $exists = OrderStatusLogSecurityCheck::get()->filter(['OrderID' => $this->getOwner()->ID])->exists();
            if (!$exists) {
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
