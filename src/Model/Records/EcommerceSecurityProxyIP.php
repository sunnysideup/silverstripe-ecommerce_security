<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityProxyIP
 *
 */
class EcommerceSecurityProxyIP extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted Proxy IP Address';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted Proxy IP Addresses';

    private static $field_labels = [
        'Title' => 'ProxyIP',
    ];
}
