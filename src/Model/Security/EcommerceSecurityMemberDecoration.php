<?php

namespace Sunnysideup\EcommerceSecurity\Model\Security;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityMemberDecoration
 *
 * @property \SilverStripe\Security\Member|\Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityMemberDecoration $owner
 * @property bool $IsWhitelisted
 * @property bool $IsSecurityRisk
 */
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
