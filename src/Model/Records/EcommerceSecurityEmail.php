<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail
 *
 */
class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted Email';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted Emails';

    private static $field_labels = [
        'Title' => 'Email',
    ];
}
