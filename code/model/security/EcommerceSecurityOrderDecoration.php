<?php

class EcommerceSecurityOrderDecoration extends DataExtension
{
    private static $db = array(
        'SkipToSecurityChecks' => 'Boolean'
    );

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->IsSubmitted()) {
            $currentStep = $this->owner->MyStep()->Sort;
            $securityStep = OrderStep::get()->filter(['ClassName' => 'OrderStep_SecurityCheck'])->first()->Sort;
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
                        )
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
            $securityCheck = OrderStatusLog_SecurityCheck::create();
            $securityCheck->OrderID = $this->owner->ID;
            $securityCheck->write();
            $securityStepID = OrderStep::get()->filter(['ClassName' => 'OrderStep_SecurityCheck'])->first()->ID;
            if($securityStepID){
                $this->owner->StatusID = $securityStepID;
            }
        }
    }
}
