<?php


class EcommerceSecurityEmail extends EcommerceSecurityBaseClass
{

    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted Email";
    public function i18n_singular_name()
    {
        return $this->Config()->get('singular_name');
    }
    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Emails";
    public function i18n_plural_name()
    {
        return $this->Config()->get('plural_name');
    }

    private static $field_labels = array(
        'Title' => 'Email'
    );
}
