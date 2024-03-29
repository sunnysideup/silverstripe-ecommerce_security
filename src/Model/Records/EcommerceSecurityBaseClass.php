<?php

namespace Sunnysideup\EcommerceSecurity\Model\Records;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Sunnysideup\Ecommerce\Forms\Fields\EcommerceClassNameOrTypeDropdownField;
use Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole;
use Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLogSecurityCheck;

/**
 * Class \Sunnysideup\EcommerceSecurity\Model\Records\EcommerceSecurityBaseClass
 *
 * @property string $Title
 * @property string $Status
 * @method \SilverStripe\ORM\ManyManyList|\Sunnysideup\EcommerceSecurity\Model\Process\OrderStatusLogSecurityCheck[] SecurityChecks()
 */
class EcommerceSecurityBaseClass extends DataObject
{

    public static function is_safe(string $string): bool
    {
        $class = static::class;
        $obj = $class::get()->filter(['Title' => $string])->Last();
        if ($obj) {
            return $obj->Status === 'Good';
        }
        return false;
    }

    public static function is_risk(string $string): bool
    {
        $class = static::class;
        $obj = $class::get()->filter(['Title' => $string])->Last();
        if ($obj) {
            return $obj->Status === 'Bad';
        }
        return false;
    }
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Blacklisted Item';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Blacklisted Items';

    private static $table_name = 'EcommerceSecurityBaseClass';

    private static $db = [
        'Title' => 'Varchar(200)',
        'Status' => 'Enum("Unknown, Good, Bad", "Unknown")',
    ];

    private static $belongs_many_many = [
        'SecurityChecks' => OrderStatusLogSecurityCheck::class,
    ];

    private static $casting = [
        'Type' => 'Varchar',
        'SimplerName' => 'Varchar',
    ];

    private static $summary_fields = [
        'Created' => 'Created',
        'LastEdited.Ago' => 'Last Edit',
        'SimplerName' => 'Type',
        'Title' => 'Value',
        'Status' => 'Status',
    ];

    private static $indexes = [
        'ClassName_Title' => [
            'type' => 'unique',
            'columns' => ['ClassName', 'Title'],
        ],
        'Title' => true,
        'ClassName' => true,
    ];

    private static $field_labels = [
        'Title' => 'Value',
    ];

    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'ClassName' => [
            'filter' => 'PartialMatchFilter',
            'title' => 'Type',
        ],
        'Status' => 'PartialMatchFilter',
    ];

    private static $default_sort = 'Status DESC';

    public function i18n_singular_name()
    {
        return Config::inst()->get($this->ClassName, 'singular_name');
    }

    public function i18n_plural_name()
    {
        return Config::inst()->get($this->ClassName, 'plural_name');
    }

    /**
     * filter value examples are:
     * ```php
     *     array('Title' => 'Foo')
     * ```
     * you can not provide multi-dimensional arrays.
     *
     * @param array $write       associative array of filter values
     * @param bool  $write       if a new one is created, should it be written
     * @param mixed $filterArray
     *
     * @return DataObject
     */
    public static function find_or_create($filterArray, $write = true)
    {
        $className = static::class;
        //we dont want empty ones so we just return a temp object...
        if (empty($filterArray['Title'])) {
            $obj = EcommerceSecurityBaseClass::create();
        } else {
            $filterArray['ClassName'] = $className;
            $obj = $className::get()->filter($filterArray)->first();
            if (!$obj) {
                $obj = $className::create($filterArray);
                if ($write) {
                    $obj->write();
                }
            }
        }

        return $obj;
    }

    public function canCreate($member = null, $context = [])
    {
        return true;
    }

    public function canView($member = null, $context = [])
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if (null !== $extended) {
            return $extended;
        }
        if (Permission::checkMember($member, Config::inst()->get(EcommerceRole::class, 'admin_permission_code'))) {
            return true;
        }

        return parent::canView($member);
    }

    public function canEdit($member = null, $context = [])
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if (null !== $extended) {
            return $extended;
        }
        if (Permission::checkMember($member, Config::inst()->get(EcommerceRole::class, 'admin_permission_code'))) {
            return true;
        }

        return parent::canEdit($member);
    }

    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * CMS Fields.
     *
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if ($this->exists()) {
            $labels = $this->fieldLabels();
            $fields->addFieldToTab(
                'Root.Main',
                $type = ReadonlyField::create('Type', 'Type', $labels['Title']),
                'Title'
            );
            $fields->replaceField(
                'Title',
                $fields->dataFieldByName('Title')->setTitle($labels['Title'])->performReadonlyTransformation()
            );
        } else {
            $availableClasses = ClassInfo::subclassesFor($this->ClassName);
            unset($availableClasses[$this->ClassName]);
            $fields->addFieldToTab(
                'Root.Main',
                EcommerceClassNameOrTypeDropdownField::create(
                    'ClassName',
                    'Type',
                    EcommerceSecurityBaseClass::class,
                    $availableClasses
                )->addExtraClass('dropdown')
            );
            $fields->dataFieldByName('Title')->setTitle('Value');
        }

        return $fields;
    }

    public function getType()
    {
        return $this->singular_name();
    }

    public function getSimplerName()
    {
        return str_replace('Blacklisted ', '', (string) $this->singular_name());
    }

    /**
     * @return bool
     */
    public function hasRisks(): bool
    {
        return $this->Title && $this->ID && 'Bad' === $this->Status ? true : false;
    }

    /**
     * @return bool
     */
    public function isSafe(): bool
    {
        return 'Good' === $this->Status ? true : false;
    }

    /**
     * @return bool
     */
    public function hasOpinion(): bool
    {
        return 'Unknown' !== $this->Status ? true : false;
    }
}
