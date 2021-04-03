<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

class EcommerceSecurityIP extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable
     * @var string
     */
    private static $singular_name = 'Blacklisted IP Address';

    /**
     * standard SS variable
     * @var string
     */
    private static $plural_name = 'Blacklisted IP Addresses';

    private static $field_labels = [
        'Title' => 'IP',
    ];
}
