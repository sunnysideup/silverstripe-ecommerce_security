<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityAddress
 *
 */
class EcommerceSecurityAddress extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted Address';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted Addresses';

    private static $field_labels = [
        'Title' => 'Address',
    ];
}
