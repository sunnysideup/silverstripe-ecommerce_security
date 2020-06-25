2020-06-25 03:39

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_security
php /var/www/ss3/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_security/ecommerce_security  --root-dir=/var/www/upgrades/ecommerce_security --write -vvv
Writing changes for 11 files
Running upgrades on "/var/www/upgrades/ecommerce_security/ecommerce_security"
[2020-06-25 15:39:41] Applying RenameClasses to _config.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to _config.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityTest.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityTest.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityLogInterface.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityLogInterface.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityIP.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityIP.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityPhone.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityPhone.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityBaseClass.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityBaseClass.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityAddress.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityAddress.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityProxyIP.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityProxyIP.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityEmail.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityEmail.php...
[2020-06-25 15:39:41] Applying RenameClasses to OrderStep_SecurityCheck.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to OrderStep_SecurityCheck.php...
[2020-06-25 15:39:41] Applying RenameClasses to OrderStatusLog_SecurityCheck.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to OrderStatusLog_SecurityCheck.php...
[2020-06-25 15:39:41] Applying RenameClasses to OrderStatusLog_WhitelistCustomer.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to OrderStatusLog_WhitelistCustomer.php...
[2020-06-25 15:39:41] Applying RenameClasses to OrderStep_WhitelistCustomer.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to OrderStep_WhitelistCustomer.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityOrderDecoration.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityOrderDecoration.php...
[2020-06-25 15:39:41] Applying RenameClasses to EcommerceSecurityMemberDecoration.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to EcommerceSecurityMemberDecoration.php...
[2020-06-25 15:39:41] Applying RenameClasses to SecurityCheck_ModelAdmin.php...
[2020-06-25 15:39:41] Applying ClassToTraitRule to SecurityCheck_ModelAdmin.php...
[2020-06-25 15:39:41] Applying UpdateConfigClasses to config.yml...
modified:	tests/EcommerceSecurityTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class EcommerceSecurityTest extends SapphireTest
 {

modified:	src/Model/Records/EcommerceSecurityBaseClass.php
@@ -2,13 +2,24 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Records;

-use DataObject;
-use Config;
-use Member;
-use Permission;
-use ReadonlyField;
-use ClassInfo;
-use EcommerceClassNameOrTypeDropdownField;
+
+
+
+
+
+
+
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_SecurityCheck;
+use SilverStripe\Security\Member;
+use Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole;
+use SilverStripe\Security\Permission;
+use SilverStripe\Forms\ReadonlyField;
+use SilverStripe\Core\ClassInfo;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityBaseClass;
+use Sunnysideup\Ecommerce\Forms\Fields\EcommerceClassNameOrTypeDropdownField;
+use SilverStripe\ORM\DataObject;
+



@@ -73,7 +84,7 @@
     );

     private static $belongs_many_many = array(
-        'SecurityChecks' => 'OrderStatusLog_SecurityCheck'
+        'SecurityChecks' => OrderStatusLog_SecurityCheck::class
     );

     private static $casting = array(
@@ -191,7 +202,7 @@
         if ($extended !== null) {
             return $extended;
         }
-        if (Permission::checkMember($member, Config::inst()->get('EcommerceRole', 'admin_permission_code'))) {
+        if (Permission::checkMember($member, Config::inst()->get(EcommerceRole::class, 'admin_permission_code'))) {
             return true;
         }

@@ -207,7 +218,7 @@
         if ($extended !== null) {
             return $extended;
         }
-        if (Permission::checkMember($member, Config::inst()->get('EcommerceRole', 'admin_permission_code'))) {
+        if (Permission::checkMember($member, Config::inst()->get(EcommerceRole::class, 'admin_permission_code'))) {
             return true;
         }

@@ -263,7 +274,7 @@
                 EcommerceClassNameOrTypeDropdownField::create(
                     'ClassName',
                     'Type',
-                    'EcommerceSecurityBaseClass',
+                    EcommerceSecurityBaseClass::class,
                     $availableClasses
                 )->addExtraClass('dropdown')
             );

Warnings for src/Model/Records/EcommerceSecurityBaseClass.php:
 - src/Model/Records/EcommerceSecurityBaseClass.php:159 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 159

 - src/Model/Records/EcommerceSecurityBaseClass.php:170 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 170

modified:	src/Model/Records/EcommerceSecurityEmail.php
@@ -1,6 +1,8 @@
 <?php

 namespace Sunnysideup\EcommerceSecurity\Model\Records;
+use SilverStripe\Control\Email\Email;
+



@@ -22,7 +24,7 @@
     private static $plural_name = "Blacklisted Emails";

     private static $field_labels = array(
-        'Title' => 'Email'
+        'Title' => Email::class
     );
 }


modified:	src/Model/Process/OrderStep_SecurityCheck.php
@@ -2,11 +2,18 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Process;

-use OrderStep;
-use OrderStepInterface;
-use Order;
-use FieldList;
-use EcommerceCMSButtonField;
+
+
+
+
+
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_SecurityCheck;
+use Sunnysideup\Ecommerce\Model\Order;
+use SilverStripe\Forms\FieldList;
+use Sunnysideup\Ecommerce\Forms\Fields\EcommerceCMSButtonField;
+use Sunnysideup\Ecommerce\Model\Process\OrderStep;
+use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
+


 /**
@@ -32,7 +39,7 @@
      *
      * @var string
      */
-    protected $relevantLogEntryClassName = 'OrderStatusLog_SecurityCheck';
+    protected $relevantLogEntryClassName = OrderStatusLog_SecurityCheck::class;

     public function getCMSFields()
     {
@@ -142,7 +149,7 @@
     {
         $fields = parent::addOrderStepFields($fields, $order);
         $title = _t('OrderStep.MUST_ACTION_SECURITY_CHECKS', ' ... To move this order to the next step you have to carry out a bunch of security checks.');
-        $field = $order->getOrderStatusLogsTableFieldEditable('OrderStatusLog_SecurityCheck', $title);
+        $field = $order->getOrderStatusLogsTableFieldEditable(OrderStatusLog_SecurityCheck::class, $title);
         $logEntry = $this->RelevantLogEntry($order);
         $link = '/admin/sales/Order/EditForm/field/Order/item/'.$order->ID.'/ItemEditForm/field/OrderStatusLog_SecurityCheck/item/'.$logEntry->ID.'/edit';
         $button = EcommerceCMSButtonField::create(

Warnings for src/Model/Process/OrderStep_SecurityCheck.php:
 - src/Model/Process/OrderStep_SecurityCheck.php:80 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 80

modified:	src/Model/Process/OrderStatusLog_SecurityCheck.php
@@ -2,20 +2,43 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Process;

-use OrderStatusLog;
+
 use GeoIP;
-use HeaderField;
-use LiteralField;
-use HTMLEditorField;
-use Order;
-use GridField;
-use OptionsetField;
-use ClassInfo;
-use Injector;
-use BillingAddress;
-use ShippingAddress;
-use EcommercePayment;
-use DBField;
+
+
+
+
+
+
+
+
+
+
+
+
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityBaseClass;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityIP;
+use SilverStripe\Forms\HeaderField;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
+use Sunnysideup\Ecommerce\Model\Order;
+use SilverStripe\Forms\GridField\GridField;
+use SilverStripe\Forms\OptionsetField;
+use Sunnysideup\EcommerceSecurity\Interfaces\EcommerceSecurityLogInterface;
+use SilverStripe\Core\ClassInfo;
+use SilverStripe\Core\Injector\Injector;
+use SilverStripe\Control\Email\Email;
+use Sunnysideup\Ecommerce\Model\Address\BillingAddress;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;
+use Sunnysideup\Ecommerce\Model\Address\ShippingAddress;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityPhone;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityAddress;
+use Sunnysideup\Ecommerce\Model\Money\EcommercePayment;
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityProxyIP;
+use SilverStripe\ORM\FieldType\DBBoolean;
+use SilverStripe\ORM\FieldType\DBField;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+



@@ -48,7 +71,7 @@
     );

     private static $many_many = array(
-        'BlacklistItems' => 'EcommerceSecurityBaseClass'
+        'BlacklistItems' => EcommerceSecurityBaseClass::class
     );

     /**
@@ -132,7 +155,7 @@
             $fields = parent::getCMSFields();
             $securityIP = "";
             foreach ($this->BlacklistItems() as $item) {
-                if (is_a($item, 'EcommerceSecurityIP')) {
+                if (is_a($item, EcommerceSecurityIP::class)) {
                     $securityIP = $item->Title;
                     break;
                 }
@@ -293,7 +316,7 @@
             );
         }
         if ($order) {
-            $implementers = ClassInfo::implementorsOf('EcommerceSecurityLogInterface');
+            $implementers = ClassInfo::implementorsOf(EcommerceSecurityLogInterface::class);
             if ($implementers) {
                 foreach ($implementers as $implementer) {
                     $class = Injector::inst()->get($implementer);
@@ -372,7 +395,7 @@
             if (!isset($similarArray[$otherOrder->ID])) {
                 $similarArray[$otherOrder->ID] = [];
             }
-            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
+            $similarArray[$otherOrder->ID][Email::class] = $otherOrder;
         }
         //check emails from billing address
         $emailArray = [];
@@ -390,10 +413,10 @@
             if (!isset($similarArray[$otherOrder->ID])) {
                 $similarArray[$otherOrder->ID] = [];
             }
-            $similarArray[$otherOrder->ID]["Email"] = $otherOrder;
+            $similarArray[$otherOrder->ID][Email::class] = $otherOrder;
         }
         //adding all emails to security checks
-        $this->blacklistCheck($emailArray, 'EcommerceSecurityEmail');
+        $this->blacklistCheck($emailArray, EcommerceSecurityEmail::class);


         //phones
@@ -434,7 +457,7 @@
             }
         }
         //adding all emails to security checks
-        $this->blacklistCheck($phoneArray, 'EcommerceSecurityPhone');
+        $this->blacklistCheck($phoneArray, EcommerceSecurityPhone::class);

         //addresses
         $addressArray = [];
@@ -475,7 +498,7 @@
                 $similarArray[$otherOrder->ID]["Address"] = $otherOrder;
             }
         }
-        $this->blacklistCheck($addressArray, 'EcommerceSecurityAddress');
+        $this->blacklistCheck($addressArray, EcommerceSecurityAddress::class);


         //IP
@@ -503,7 +526,7 @@
                 }
                 $similarArray[$otherOrder->ID]["IP"] = $otherOrder;
             }
-            $this->blacklistCheck($ipArray, 'EcommerceSecurityIP');
+            $this->blacklistCheck($ipArray, EcommerceSecurityIP::class);
         }
         if (count($ipProxyArray)) {
             //are there any orders with the same Proxy in the xxx seven days...
@@ -517,7 +540,7 @@
                 }
                 $similarArray[$otherOrder->ID]["ProxyIP"] = $otherOrder;
             }
-            $this->blacklistCheck($ipProxyArray, 'EcommerceSecurityProxyIP');
+            $this->blacklistCheck($ipProxyArray, EcommerceSecurityProxyIP::class);
         }


@@ -557,7 +580,7 @@

     public function getSecurityCleared()
     {
-        return  DBField::create_field('Boolean', ($this->pass() ? true : false));
+        return  DBField::create_field(DBBoolean::class, ($this->pass() ? true : false));
     }

     /**

Warnings for src/Model/Process/OrderStatusLog_SecurityCheck.php:
 - src/Model/Process/OrderStatusLog_SecurityCheck.php:684 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 684

modified:	src/Model/Process/OrderStatusLog_WhitelistCustomer.php
@@ -2,10 +2,16 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Process;

-use OrderStatusLog;
-use CMSEditLinkField;
-use Member;
-use Order;
+
+
+
+
+use SilverStripe\Security\Member;
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_WhitelistCustomer;
+use Sunnysideup\CmsEditLinkField\Forms\Fields\CMSEditLinkField;
+use Sunnysideup\Ecommerce\Model\Order;
+use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
+



@@ -41,8 +47,8 @@
     );

     private static $has_one = array(
-        'Member' => 'Member',
-        'BasedOn' => 'OrderStatusLog_WhitelistCustomer'
+        'Member' => Member::class,
+        'BasedOn' => OrderStatusLog_WhitelistCustomer::class
     );

     private static $defaults = array(

modified:	src/Model/Process/OrderStep_WhitelistCustomer.php
@@ -2,9 +2,14 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Process;

-use OrderStep;
-use OrderStepInterface;
-use Order;
+
+
+
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_WhitelistCustomer;
+use Sunnysideup\Ecommerce\Model\Order;
+use Sunnysideup\Ecommerce\Model\Process\OrderStep;
+use Sunnysideup\Ecommerce\Interfaces\OrderStepInterface;
+


 class OrderStep_WhitelistCustomer extends OrderStep implements OrderStepInterface
@@ -24,7 +29,7 @@
      *
      * @var string
      */
-    protected $relevantLogEntryClassName = 'OrderStatusLog_WhitelistCustomer';
+    protected $relevantLogEntryClassName = OrderStatusLog_WhitelistCustomer::class;

     public function getCMSFields()
     {

Warnings for src/Model/Process/OrderStep_WhitelistCustomer.php:
 - src/Model/Process/OrderStep_WhitelistCustomer.php:79 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 79

modified:	src/Model/Security/EcommerceSecurityOrderDecoration.php
@@ -2,12 +2,20 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Security;

-use DataExtension;
-use FieldList;
-use OrderStep;
-use HeaderField;
-use CheckboxField;
-use OrderStatusLog_SecurityCheck;
+
+
+
+
+
+
+use SilverStripe\Forms\FieldList;
+use Sunnysideup\Ecommerce\Model\Process\OrderStep;
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStep_SecurityCheck;
+use SilverStripe\Forms\HeaderField;
+use SilverStripe\Forms\CheckboxField;
+use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_SecurityCheck;
+use SilverStripe\ORM\DataExtension;
+



@@ -43,7 +51,7 @@
     {
         if ($this->owner->IsSubmitted()) {
             $currentStep = $this->owner->MyStep()->Sort;
-            $securityStep = OrderStep::get()->filter(['ClassName' => 'OrderStep_SecurityCheck'])->first()->Sort;
+            $securityStep = OrderStep::get()->filter(['ClassName' => OrderStep_SecurityCheck::class])->first()->Sort;
             if (! $this->owner->IsPaid() && $currentStep < $securityStep) {
                 $fields->addFieldsToTab(
                     'Root.Next',
@@ -80,7 +88,7 @@
                 $securityCheck = OrderStatusLog_SecurityCheck::create();
                 $securityCheck->OrderID = $this->owner->ID;
                 $securityCheck->write();
-                $securityStepID = OrderStep::get()->filter(['ClassName' => 'OrderStep_SecurityCheck'])->first()->ID;
+                $securityStepID = OrderStep::get()->filter(['ClassName' => OrderStep_SecurityCheck::class])->first()->ID;
                 if($securityStepID){
                     $this->owner->StatusID = $securityStepID;
                 }

modified:	src/Model/Security/EcommerceSecurityMemberDecoration.php
@@ -2,8 +2,11 @@

 namespace Sunnysideup\EcommerceSecurity\Model\Security;

-use DataExtension;
-use EcommerceSecurityEmail;
+
+
+use Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityEmail;
+use SilverStripe\ORM\DataExtension;
+




modified:	src/Cms/SecurityCheck_ModelAdmin.php
@@ -2,7 +2,9 @@

 namespace Sunnysideup\EcommerceSecurity\Cms;

-use ModelAdminEcommerceBaseClass;
+
+use Sunnysideup\Ecommerce\Cms\ModelAdminEcommerceBaseClass;
+




modified:	_config/config.yml
@@ -7,30 +7,22 @@
   - 'cms/*'
   - 'ecommerce/*'
 ---
-
 SilverStripe\Security\Member:
   extensions:
-    - EcommerceSecurityMemberDecoration
-
-SecurityCheck_ModelAdmin:
+    - Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityMemberDecoration
+Sunnysideup\EcommerceSecurity\Cms\SecurityCheck_ModelAdmin:
   managed_models:
-    - EcommerceSecurityBaseClass
-
-
-EcommerceRole:
+    - Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityBaseClass
+Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole:
   admin_role_permission_codes:
     - CMS_ACCESS_SecurityCheck_ModelAdmin
-
-Order:
+Sunnysideup\Ecommerce\Model\Order:
   extensions:
-    - EcommerceSecurityOrderDecoration
-
-OrderStatusLog:
+    - Sunnysideup\EcommerceSecurity\Model\Security\EcommerceSecurityOrderDecoration
+Sunnysideup\Ecommerce\Model\Process\OrderStatusLog:
   available_log_classes_array:
-    - OrderStatusLog_SecurityCheck
-    - OrderStatusLog_WhitelistCustomer
-
-
+    - Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_SecurityCheck
+    - Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLog_WhitelistCustomer
 ---
 After:
   - 'framework/*'
@@ -43,5 +35,5 @@
 SilverStripe\Admin\LeftAndMain:
   menu_groups:
     Shop:
-      - SecurityCheck_ModelAdmin
+      - Sunnysideup\EcommerceSecurity\Cms\SecurityCheck_ModelAdmin


Writing changes for 11 files
✔✔✔