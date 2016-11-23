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
        return $this->Config()->get('singular_name');
    }
    /**
     * standard SS variable
     * @Var String
     */
    private static $plural_name = "Blacklisted Items";
    public function i18n_plural_name()
    {
        return $this->Config()->get('plural_name');
    }

    private static $db = array(
        'Title' => 'Varchar(200)',
        'Status' => 'Enum("Unknown, Good, Bad", "Unknown")'
    );

    private static $casting = array(
        'Type' => 'Varchar',
        'SimplerName' => 'Varchar'
    );

    private static $summary_fields = array(
        'SimplerName' => 'Type',
        'Title' => 'Value',
        'Status' => 'Status'
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
        if (empty($filterArray['Title'])) {
            return EcommerceSecurityBaseClass::create();
        }
        $obj = $className::get()->filter($filterArray)->first();
        if (! $obj) {
            $obj = $className::create($filterArray);
            if ($write) {
                $obj->write();
            }
        }
        return $obj;
    }

    public function canCreate($member = null)
    {
        return true;
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
        return $this->Status == 'Bad' ? true : false;
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
        $rows = DB::query('SHOW INDEX FROM EcommerceSecurityBaseClass WHERE Key_name = \'MyUniqueIndex\';');
        $count = 0;
        foreach ($rows as $row) {
            $count++;
        }
        if (! $count) {
            DB::query('
                ALTER TABLE "EcommerceSecurityBaseClass" ADD unique "MyUniqueIndex" ("ClassName","Title")
            ');
        } elseif ($count !== 2) {
            user_error('EcommerceSecurityBaseClass.MyUniqueIndex not set correctly.');
        }
    }
}
