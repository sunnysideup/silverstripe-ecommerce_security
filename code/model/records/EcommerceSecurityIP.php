<?php


class EcommerceSecurityIP extends EcommerceSecurityBaseClass
{

    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted IP Address";

    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted IP Addresses";


    private static $field_labels = array(
        'Title' => 'IP'
    );
}
