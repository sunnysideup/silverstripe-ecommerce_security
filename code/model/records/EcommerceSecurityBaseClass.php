<?php


class EcommerceSecurityBaseClass extends DataObject
{


    /**
     * standard SS variable
     * @Var String
     */
    private static $singular_name = "Blacklisted Item";
    public function i18n_singular_name()
    {
        return Config::inst()->get($this->ClassName, 'singular_name');
    }
    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Items";
    public function i18n_plural_name()
    {
        return Config::inst()->get($this->ClassName, 'plural_name');
    }

    private static $db = array(
        'Title' => 'Varchar(200)',
        'Status' => 'Enum("Unknown, Good, Bad", "Unknown")'
    );

    private static $belongs_many_many = array(
        'SecurityChecks' => 'OrderStatusLog_SecurityCheck'
    );

    private static $casting = array(
        'Type' => 'Varchar',
        'SimplerName' => 'Varchar'
    );

    private static $summary_fields = array(
        'Created' => 'Created',
        'LastEdited.Ago' => 'Last Edit',
        'SimplerName' => 'Type',
        'Title' => 'Value',
        'Status' => 'Status'
    );

    private static $indexes = array(
        'ClassName_Title' => array('type' => 'unique', 'value' => '"ClassName","Title"'),
        'Title' => true,
        'ClassName' => true,
    );

    private static $field_labels = array(
        'Title' => 'Value'
    );

    private static $searchable_fields = array(
        'Title' => 'PartialMatchFilter',
        'ClassName' => array(
            'filter' => 'PartialMatchFilter',
            'title' => 'Type'
        ),
        'Status' => 'PartialMatchFilter'
    );

    private static $default_sort = 'Status DESC';

    /**
     * filter value examples are:
     * ```php
     *     array('Title' => 'Foo')
     * ```
     * you can not provide multi-dimensional arrays
     *
     * @param  array $filterArray  associative array of filter values
     * @param  bool $filterArray   if a new one is created, should it be written
     * @return DataObject
     */
    public static function find_or_create($filterArray, $write = true)
    {
        $className = get_called_class();
        //we dont want empty ones so we just return a temp object...
        if (empty($filterArray['Title'])) {
            $obj = EcommerceSecurityBaseClass::create();
        } else {
            $filterArray['ClassName'] = $className;
            $obj = $className::get()->filter($filterArray)->first();
            if (! $obj) {
                $obj = $className::create($filterArray);
                if ($write) {
                    $obj->write();
                }
            }
        }

        return $obj;
    }

    public function canCreate($member = null)
    {
        return true;
    }

    public function canView($member = null)
    {
        if (! $member) {
            $member = Member::currentUser();
        }
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        if (Permission::checkMember($member, Config::inst()->get('EcommerceRole', 'admin_permission_code'))) {
            return true;
        }

        return parent::canView($member);
    }

    public function canEdit($member = null)
    {
        if (! $member) {
            $member = Member::currentUser();
        }
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        if (Permission::checkMember($member, Config::inst()->get('EcommerceRole', 'admin_permission_code'))) {
            return true;
        }

        return parent::canEdit($member);
    }

    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * CMS Fields
     * @return FieldList
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
                $fields->dataFieldByName('Title')->setTitle($labels["Title"])->performReadonlyTransformation()
            );
        } else {
            $availableClasses = ClassInfo::subclassesFor($this->ClassName);
            unset($availableClasses[$this->ClassName]);
            $fields->addFieldToTab(
                'Root.Main',
                EcommerceClassNameOrTypeDropdownField::create(
                    'ClassName',
                    'Type',
                    'EcommerceSecurityBaseClass',
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
        return str_replace('Blacklisted ', '', $this->singular_name());
    }

    /**
     *
     *
     * @return bool
     */
    public function hasRisks()
    {
        return $this->Title && $this->ID && $this->Status == 'Bad' ? true : false;
    }

    /**
     *
     *
     * @return bool
     */
    public function isSafe()
    {
        return $this->Status == 'Good' ? true : false;
    }

    /**
     *
     *
     * @return bool
     */
    public function hasOpinion()
    {
        return $this->Status !== 'Unknown' ? true : false;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
    }
}

