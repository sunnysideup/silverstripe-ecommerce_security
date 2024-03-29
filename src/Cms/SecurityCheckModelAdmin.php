<?php

namespace Sunnysideup\EcommerceSecurity\Cms;

use SilverStripe\Admin\ModelAdmin;
use Sunnysideup\Ecommerce\Traits\EcommerceModelAdminTrait;

/**
 * Class \Sunnysideup\EcommerceSecurity\Cms\SecurityCheckModelAdmin
 *
 */
class SecurityCheckModelAdmin extends ModelAdmin
{
    use EcommerceModelAdminTrait;

    /**
     * Change this variable if you don't want the Import from CSV form to appear.
     * This variable can be a boolean or an array.
     * If array, you can list className you want the form to appear on. i.e. array('myClassOne','myClasstwo').
     */
    public $showImportForm = false;

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $url_segment = 'security-checks';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $menu_title = 'Security Checks';

    /**
     * standard SS variable.
     *
     * @var int
     */
    private static $menu_priority = 3.0;

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $menu_icon = 'vendor/sunnysideup/ecommerce/client/images/icons/money-file.gif';
}
