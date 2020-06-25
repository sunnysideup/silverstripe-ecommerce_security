<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_SecurityCheck;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStep_SecurityCheck;

/**
 * ### @@@@ START REPLACEMENT @@@@ ###
 * WHY: automated upgrade
 * OLD:  extends DataExtension (ignore case)
 * NEW:  extends DataExtension (COMPLEX)
 * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
 * ### @@@@ STOP REPLACEMENT @@@@ ###
 */
class EcommerceSecurityOrderDecoration extends DataExtension
{
    /**
     * ### @@@@ START REPLACEMENT @@@@ ###
     * OLD: private static $db (case sensitive)
     * NEW:
    private static $db (COMPLEX)
     * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
     * ### @@@@ STOP REPLACEMENT @@@@ ###
     */
    private static $table_name = 'EcommerceSecurityOrderDecoration';

    private static $db = [
        'SkipToSecurityChecks' => 'Boolean',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->IsSubmitted()) {
            $currentStep = $this->owner->MyStep()->Sort;
            $securityStep = OrderStep::get()->filter(['ClassName' => OrderStep_SecurityCheck::class])->first()->Sort;
            if (! $this->owner->IsPaid() && $currentStep < $securityStep) {
                $fields->addFieldsToTab(
                    'Root.Next',
                    [
                        HeaderField::create(
                            'SkipToSecurityChecksHeader',
                            'Skip To Security Checks'
                        ),
                        CheckboxField::create(
                            'SkipToSecurityChecks',
                            'Skip To Security Checks'
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
            $logCount = OrderStatusLog_SecurityCheck::get()->filter(['OrderID' => $this->owner->ID])->count();
            if ($logCount) {
                //do nothing - the security check already exists
            } else {
                $securityCheck = OrderStatusLog_SecurityCheck::create();
                $securityCheck->OrderID = $this->owner->ID;
                $securityCheck->write();
                $securityStepID = OrderStep::get()->filter(['ClassName' => OrderStep_SecurityCheck::class])->first()->ID;
                if ($securityStepID) {
                    $this->owner->StatusID = $securityStepID;
                }
            }
        }
    }
}
