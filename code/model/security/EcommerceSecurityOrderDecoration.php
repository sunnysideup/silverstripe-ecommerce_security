<?php

class EcommerceSecurityOrderDecoration extends DataExtension
{
    private static $db = array(
        'SkipPayment' => 'Boolean'
    );

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->IsSubmitted()) {
            if (! $this->owner->IsPaid()) {
                $fields->addFieldsToTab(
                    'Root.Payments',
                    [
                        HeaderField::create(
                            'SkipToSecurityChecks',
                            'Skip To Security Checks'
                        ),
                        CheckboxField::create(
                            'SkipPayment',
                            'Skip Payment'
                        )->setDescription(
                            'Ticking this checkbox will add a fake "successful" payment to the order,  this allows the order to proceed to the security checks step.
                            <br>Make sure to save the order after ticking this checkbox.'
                        )
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
        if ($this->owner->SkipPayment) {
            $money = Money::create();
            $money->setAmount($this->owner->TotalOutstanding());
            $payment = EcommercePayment::create();
            $payment->Status = 'Success';
            $payment->Amount = $money;
            $payment->Message = 'This is a fake payment that has been created to allow the order to proceed to the next step, this order has not really been paid for.';
            $payment->write();
            $this->owner->Payments()->add($payment);
        }
    }
}
