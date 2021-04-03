<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

class EcommerceSecurityAddress extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable
     * @var String
     */
    private static $singular_name = 'Blacklisted Address';

    /**
     * standard SS variable
     * @var String
     */
    private static $plural_name = 'Blacklisted Addresses';

    private static $field_labels = [
        'Title' => 'Address',
    ];
}
