<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

class EcommerceSecurityProxyIP extends EcommerceSecurityBaseClass
{
    /**
     * standard SS variable
     * @var String
     */
    private static $singular_name = 'Blacklisted Proxy IP Address';

    /**
     * standard SS variable
     * @var String
     */
    private static $plural_name = 'Blacklisted Proxy IP Addresses';

    private static $field_labels = [
        'Title' => 'ProxyIP',
    ];
}
