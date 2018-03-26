<?php
/**
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: buyables
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
interface EcommerceSecurityLogInterface
{
    /**
     * if does not return NULL, then a tab will be created in ecom Sec. with the
     * actual OrderStatusLog entry or entries
     * @return FormField|null
     */
    public function getSecurityLogTable();

    /**
     * the name of the where the SecurityLogTable will be added if getSecurityLogTable returns a formField
     * @return string
     */
    public function getSecurityLogTableTabName();

    /**
     * returns a summary without header for the Ecom Sec. Main summary Page
     * @return LiteralField (html)
     */
    public function getSecuritySummary();

    /**
     * returns the header to be used in TAB and in Summary Page (on the Ecom Security Module)
     * @return HeaderField
     */
    public function getSecurityHeader();
}
