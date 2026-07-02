<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Member;
use SilverStripe\Forms\FieldList;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityMemberDecoration
 *
 * @property Member|EcommerceSecurityMemberDecoration $owner
 * @property bool $IsWhitelisted
 * @property bool $IsSecurityRisk
 */
class EcommerceSecurityMemberDecoration extends Extension
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
        $owner = $this->getOwner();
        if ($owner->IsSecurityRisk) {
            $owner->IsWhitelisted = false;
            $filter = ['Title' => $owner->Email];
            $securityCheck = EcommerceSecurityEmail::get()->filter($filter)->first();
            if (!$securityCheck) {
                $securityCheck = EcommerceSecurityEmail::create($filter);
            }

            $securityCheck->Status = 'Bad';
            $securityCheck->write();
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        $fields->addFieldsToTab(
            'Root.Security',
            [
                $fields->dataFieldByName('IsWhitelisted'),
                $fields->dataFieldByName('IsSecurityRisk'),
            ]
        );

        return $fields;
    }
}
