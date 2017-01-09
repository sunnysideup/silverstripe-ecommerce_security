<?php


class EcommerceSecurityProxyIP extends EcommerceSecurityBaseClass
{


    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted Proxy IP Address";

    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Proxy IP Addresses";

    private static $field_labels = array(
        'Title' => 'ProxyIP'
    );
}
