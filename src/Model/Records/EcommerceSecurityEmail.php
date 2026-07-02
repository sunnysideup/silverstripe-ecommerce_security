<?php

declare(strict_types=1);

namespace Sunnysideup\EcommerceSecurity\Model\Records;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail
 *
 */
class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{
    private static $table_name = 'EcommerceSecurityEmail';

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
