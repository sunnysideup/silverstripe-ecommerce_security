<?php

namespace Sunnysideup\EcommerceSecurity\Interfaces;

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
     *
     * @param Order $order
     *
     * @return FormField|null
     */
    public function getSecurityLogTable($order);

    /**
     * the name of the where the SecurityLogTable will be added if getSecurityLogTable returns a formField
     * @return string
     */
    public function getSecurityLogTableTabName();

    /**
     * returns a summary without header for the Ecom Sec. Main summary Page
     *
     * @param Order $order
     *
     * @return LiteralField (html)
     */
    public function getSecuritySummary($order);

    /**
     * returns the header to be used in TAB and in Summary Page (on the Ecom Security Module)
     * @return HeaderField
     */
    public function getSecurityHeader();
}
