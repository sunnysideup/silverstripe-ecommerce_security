<?php


class EcommerceSecurityProxyIP extends EcommerceSecurityBaseClass
{


    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted Proxy IP Address";
        function i18n_singular_name() { return $this->Config()->get('singular_name');}
    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Proxy IP Addresses";
        function i18n_plural_name() { return $this->Config()->get('plural_name');}

    private static $field_labels = array(
        'Title' => 'ProxyIP'
    );

}
