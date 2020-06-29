<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\ORM\DataExtension;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;

class EcommerceSecurityMemberDecoration extends DataExtension
{
    private static $db = [
        'IsWhitelisted' => 'Boolean',
        'IsSecurityRisk' => 'Boolean',
    ];

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->IsSecurityRisk) {
            $this->owner->IsWhitelisted = false;
            $securityCheck = EcommerceSecurityEmail::get()->filter(['Title' => $this->owner->Email])->first();
            if ($securityCheck) {
                $securityCheck->Status = 'Bad';
            } else {
                $securityCheck = EcommerceSecurityEmail::create();
                $securityCheck->Title = $this->owner->Email;
                $securityCheck->Status = 'Bad';
            }
            $securityCheck->write();
        }
    }
}
