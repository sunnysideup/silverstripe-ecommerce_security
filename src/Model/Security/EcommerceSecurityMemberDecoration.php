<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;



use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;
use SilverStripe\ORM\DataExtension;




/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class EcommerceSecurityMemberDecoration extends DataExtension
{

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'EcommerceSecurityMemberDecoration';

    private static $db = array(
        'IsWhitelisted' => 'Boolean',
        'IsSecurityRisk' => 'Boolean'
    );

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

