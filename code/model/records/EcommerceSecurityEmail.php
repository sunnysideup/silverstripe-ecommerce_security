<?php


class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{

    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted Email";

    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Emails";

    private static $field_labels = array(
        'Title' => 'Email'
    );
}

