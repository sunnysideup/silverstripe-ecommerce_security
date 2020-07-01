<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

use SilverStripe\Control\Email\Email;

class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = 'Blacklisted Email';

    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = 'Blacklisted Emails';

    private static $field_labels = [
        'Title' => 'Email',
    ];
}
