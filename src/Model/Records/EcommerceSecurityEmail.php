<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable
     * @var string
     */
    private static $singular_name = 'Blacklisted Email';

    /**
     * standard SS variable
     * @var string
     */
    private static $plural_name = 'Blacklisted Emails';

    private static $field_labels = [
        'Title' => 'Email',
    ];
}
