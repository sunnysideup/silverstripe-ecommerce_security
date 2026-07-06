<?php

declare(strict_types=1);

namespace Sunnysideup\EcommerceSecurity\Model\Records;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityIP
 *
 */
class EcommerceSecurityIP extends EcommerceSecurityBaseClass
{
    private static $table_name = 'EcommerceSecurityIP';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted IP Address';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted IP Addresses';

    private static $field_labels = [
        'Title' => 'IP',
    ];
}
