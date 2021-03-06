<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

class EcommerceSecurityPhone extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted Phone';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted Phones';

    private static $field_labels = [
        'Title' => 'Phone',
    ];
}
