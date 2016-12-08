<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage Products
 */

/**
 * Attribute to relate to a product.
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license see license file in modules root directory
 */
class SilvercartProductAttribute extends DataObject {
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = array(
        'CanBeUsedForFilterWidget'     => 'Boolean(1)',
        'CanBeUsedForDataSheet'        => 'Boolean(1)',
        'CanBeUsedForVariants'         => 'Boolean',
        'CanBeUsedForSingleVariants'   => 'Boolean',
        'IsUserInputField'             => 'Boolean',
        'UserInputFieldMustBeFilledIn' => 'Boolean',
    );

    /**
     * has-many relations
     *
     * @var array
     */
    private static $has_many = array(
        'SilvercartProductAttributeLanguages'   => 'SilvercartProductAttributeLanguage',
        'SilvercartProductAttributeValues'      => 'SilvercartProductAttributeValue',
    );
    
    /**
     * belongs-many-many relations
     *
     * @var array
     */
    private static $belongs_many_many = array(
        'SilvercartProducts'                => 'SilvercartProduct',
        'SilvercartProductAttributeSets'    => 'SilvercartProductAttributeSet',
    );

    /**
     * Castings
     *
     * @var array
     */
    private static $casting = array(
        'Title'                                     => 'Text',
        'PluralTitle'                               => 'Text',
        'SilvercartProductAttributeSetsAsString'    => 'Text',
        'SilvercartProductAttributeValuesAsString'  => 'Text',
        'HasSelectedValues'                         => 'Boolean',
        'CanBeUsedForFilterWidgetString'            => 'Text',
        'CanBeUsedForDataSheetString'               => 'Text',
        'CanBeUsedForVariantsString'                => 'Text',
        'CanBeUsedForSingleVariantsString'          => 'Text',
    );
    
    /**
     * DB indexes
     * 
     * @var array 
     */
    private static $indexes = array(
        'CanBeUsedForFilterWidget'   => '("CanBeUsedForFilterWidget")',
        'CanBeUsedForDataSheet'      => '("CanBeUsedForDataSheet")',
        'CanBeUsedForVariants'       => '("CanBeUsedForVariants")',
        'CanBeUsedForSingleVariants' => '("CanBeUsedForSingleVariants")',
    );

        /**
     * Default sort fields and directions
     *
     * @var string
     */
    private static $default_sort = '"SilvercartProductAttribute"."CanBeUsedForVariants" DESC, "SilvercartProductAttributeLanguage"."Title"';
    
    /**
     * Assigned values
     *
     * @var ArrayList
     */
    protected $assignedValues = null;
    
    /**
     * Unassigned values
     *
     * @var ArrayList
     */
    protected $unAssignedValues = null;
    
    /**
     * Returns the translated title
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.05.2012
     */
    public function getTitle() {
        return $this->getLanguageFieldValue('Title');
    }
    
    /**
     * Returns the translated plural title
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function getPluralTitle() {
        $pluralTitle = $this->getLanguageFieldValue('PluralTitle');
        if (empty($pluralTitle)) {
            // fall back to title
            $pluralTitle = $this->getTitle();
        }
        return $pluralTitle;
    }

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            array(
                'CanBeUsedForFilterWidget'              => _t('SilvercartProductAttribute.CAN_BE_USED_FOR_FILTERWIDGET'),
                'CanBeUsedForDataSheet'                 => _t('SilvercartProductAttribute.CAN_BE_USED_FOR_DATASHEET'),
                'CanBeUsedForVariants'                  => _t('SilvercartProductAttribute.CAN_BE_USED_FOR_VARIANTS'),
                'CanBeUsedForVariantsDesc'              => _t('SilvercartProductAttribute.CAN_BE_USED_FOR_VARIANTS_DESC'),
                'CanBeUsedForFilterWidgetShort'         => _t('SilvercartProductAttribute.CanBeUsedForFilterWidgetShort'),
                'CanBeUsedForDataSheetShort'            => _t('SilvercartProductAttribute.CanBeUsedForDataSheetShort'),
                'CanBeUsedForVariantsShort'             => _t('SilvercartProductAttribute.CanBeUsedForVariantsShort'),
                'CanBeUsedForSingleVariants'            => _t('SilvercartProductAttribute.CanBeUsedForSingleVariants'),
                'CanBeUsedForSingleVariantsDesc'        => _t('SilvercartProductAttribute.CanBeUsedForSingleVariantsDesc'),
                'CanBeUsedForSingleVariantsShort'       => _t('SilvercartProductAttribute.CanBeUsedForSingleVariantsShort'),
                'Title'                                 => _t('SilvercartProductAttribute.TITLE'),
                'PluralTitle'                           => _t('SilvercartProductAttribute.PLURALTITLE'),
                'SilvercartProductAttributeLanguages'   => _t('SilvercartProductAttributeLanguage.PLURALNAME'),
                'SilvercartProductAttributeValues'      => _t('SilvercartProductAttributeValue.PLURALNAME'),
                'SilvercartProducts'                    => _t('SilvercartProduct.PLURALNAME'),
                'SilvercartProductAttributeSets'        => _t('SilvercartProductAttributeSet.PLURALNAME'),
                'ImportList'                            => _t('SilvercartProductAttribute.ImportList'),
                'ImportListDesc'                        => _t('SilvercartProductAttribute.ImportListDesc'),
                'ImportPrefix'                          => _t('SilvercartProductAttribute.ImportPrefix'),
                'ImportPrefixDesc'                      => _t('SilvercartProductAttribute.ImportPrefixDesc'),
                'ImportSuffix'                          => _t('SilvercartProductAttribute.ImportSuffix'),
                'ImportSuffixDesc'                      => _t('SilvercartProductAttribute.ImportSuffixDesc'),
                'IsUserInputField'                      => _t('SilvercartProductAttribute.IsUserInputField'),
                'IsUserInputFieldDesc'                  => _t('SilvercartProductAttribute.IsUserInputFieldDesc'),
                'UserInputFieldMustBeFilledIn'          => _t('SilvercartProductAttribute.UserInputFieldMustBeFilledIn'),
                'UserInputFieldMustBeFilledInDesc'      => _t('SilvercartProductAttribute.UserInputFieldMustBeFilledInDesc'),
            )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList the fields for the backend
     */
    public function getCMSFields() {
        $fields = SilvercartDataObject::getCMSFields($this, 'CanBeUsedForFilterWidget', false);
        
        $fields->dataFieldByName('CanBeUsedForVariants')->setDescription($this->fieldLabel('CanBeUsedForVariantsDesc'));
        $fields->dataFieldByName('CanBeUsedForSingleVariants')->setDescription($this->fieldLabel('CanBeUsedForSingleVariantsDesc'));
        $fields->dataFieldByName('IsUserInputField')->setDescription($this->fieldLabel('IsUserInputFieldDesc'));
        $fields->dataFieldByName('UserInputFieldMustBeFilledIn')->setDescription($this->fieldLabel('UserInputFieldMustBeFilledInDesc'));
        
        if ($this->exists()) {
            $importListField = new TextareaField('ImportList', $this->fieldLabel('ImportList'));
            $importListField->setDescription($this->fieldLabel('ImportListDesc'));
            $importPrefixField = new TextField('ImportPrefix', $this->fieldLabel('ImportPrefix'));
            $importPrefixField->setDescription($this->fieldLabel('ImportPrefixDesc'));
            $importSuffixField = new TextField('ImportSuffix', $this->fieldLabel('ImportSuffix'));
            $importSuffixField->setDescription($this->fieldLabel('ImportSuffixDesc'));
            
            $fields->addFieldToTab('Root.SilvercartProductAttributeValues', $importListField);
            $fields->addFieldToTab('Root.SilvercartProductAttributeValues', $importPrefixField);
            $fields->addFieldToTab('Root.SilvercartProductAttributeValues', $importSuffixField);
        }
        
        return $fields;
    }
    
    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string the objects plural name
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function plural_name() {
        return SilvercartTools::plural_name_for($this);
    }

    /**
     * Searchable fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function searchableFields() {
        $searchableFields = array(
            'SilvercartProductAttributeLanguages.Title' => array(
                'title'     => $this->fieldLabel('Title'),
                'filter'    => 'PartialMatchFilter'
            ),
            'CanBeUsedForFilterWidget' => array(
                'title'     => $this->fieldLabel('CanBeUsedForFilterWidget'),
                'filter'    => 'ExactMatchFilter'
            ),
            'CanBeUsedForDataSheet' => array(
                'title'     => $this->fieldLabel('CanBeUsedForDataSheet'),
                'filter'    => 'ExactMatchFilter'
            ),
            'CanBeUsedForVariants' => array(
                'title'     => $this->fieldLabel('CanBeUsedForVariants'),
                'filter'    => 'ExactMatchFilter'
            ),
            'CanBeUsedForSingleVariants' => array(
                'title'     => $this->fieldLabel('CanBeUsedForSingleVariants'),
                'filter'    => 'ExactMatchFilter'
            ),
        );
        $this->extend('updateSearchableFields', $searchableFields);
        return $searchableFields;
    }
    
    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function singular_name() {
        return SilvercartTools::singular_name_for($this);
    }

    /**
     * Summaryfields for display in tables.
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function summaryFields() {
        $summaryFields = array(
            'Title'                                     => $this->fieldLabel('Title'),
            'PluralTitle'                               => $this->fieldLabel('PluralTitle'),
            'CanBeUsedForFilterWidgetString'            => $this->fieldLabel('CanBeUsedForFilterWidgetShort'),
            'CanBeUsedForDataSheetString'               => $this->fieldLabel('CanBeUsedForDataSheetShort'),
            'CanBeUsedForVariantsString'                => $this->fieldLabel('CanBeUsedForVariantsShort'),
            'CanBeUsedForSingleVariantsString'          => $this->fieldLabel('CanBeUsedForSingleVariantsShort'),
            'SilvercartProductAttributeValuesAsString'  => $this->fieldLabel('SilvercartProductAttributeValues'),
        );
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Check for import values after writing an attribute in backend.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.11.2014
     */
    protected function onAfterWrite() {
        parent::onAfterWrite();
        $request    = Controller::curr()->getRequest();
        $importList = $request->postVar('ImportList');
        if (!is_null($importList) &&
            !empty($importList)) {
            $prefix = $request->postVar('ImportPrefix');
            $suffix = $request->postVar('ImportSuffix');
            if (empty($prefix)) {
                $prefix = '';
            }
            if (empty($suffix)) {
                $suffix = '';
            }
            $attributeValues = explode(PHP_EOL, $importList);
            foreach ($attributeValues as $attributeValueTitle) {
                $attributeValueTitle = trim($attributeValueTitle);
                if ($this->SilvercartProductAttributeValues()->find('Title', $attributeValueTitle)) {
                    continue;
                }
                $attributeValue = new SilvercartProductAttributeValue();
                $attributeValue->Title = $prefix . $attributeValueTitle . $suffix;
                $attributeValue->write();
                $this->SilvercartProductAttributeValues()->add($attributeValue);
            }
        }
    }
    
    /**
     * Requires this modules default records.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function requireDefaultRecords() {
        $importer = new SilvercartProductAttribute_VariantImporter();
        $importer->doImport();
    }
    
    /**
     * Returns the product attribute sets as a comma separated string
     *
     * @return string
     */
    public function getSilvercartProductAttributeSetsAsString() {
        $silvercartProductAttributeSetsArray    = $this->SilvercartProductAttributeSets()->map();
        $silvercartProductAttributeSetsAsString = implode(', ', $silvercartProductAttributeSetsArray);
        return $silvercartProductAttributeSetsAsString;
    }
    
    /**
     * Returns the product attribute values as a comma separated string
     *
     * @return string
     */
    public function getSilvercartProductAttributeValuesAsString() {
        $limit                                      = 3;
        $silvercartProductAttributeValuesAsString   = '';
        $addition                                   = '';
        if ($this->SilvercartProductAttributeValues()->Count() > 0) {
            if ($this->SilvercartProductAttributeValues()->Count() > $limit) {
                $silvercartProductAttributeValuesMap = $this->SilvercartProductAttributeValues()->limit($limit)->map();
                $addition = ' (und ' . ($this->SilvercartProductAttributeValues()->Count() - $limit) . ' weitere)';
            } else {
                $silvercartProductAttributeValuesMap = $this->SilvercartProductAttributeValues()->map();
            }
            $silvercartProductAttributeValuesAsString   = '"' . implode('", "', $silvercartProductAttributeValuesMap->toArray()) . '"';
            $silvercartProductAttributeValuesAsString   = stripslashes($silvercartProductAttributeValuesAsString);
        }
        return $silvercartProductAttributeValuesAsString . $addition;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * filter widget
     *
     * @return string
     */
    public function getCanBeUsedForFilterWidgetString() {
        $CanBeUsedForFilterWidget = _t('Silvercart.NO');
        if ($this->CanBeUsedForFilterWidget) {
            $CanBeUsedForFilterWidget = _t('Silvercart.YES');
        }
        return $CanBeUsedForFilterWidget;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * data sheet
     *
     * @return string
     */
    public function getCanBeUsedForDataSheetString() {
        $CanBeUsedForDataSheet = _t('Silvercart.NO');
        if ($this->CanBeUsedForDataSheet) {
            $CanBeUsedForDataSheet = _t('Silvercart.YES');
        }
        return $CanBeUsedForDataSheet;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * variants
     *
     * @return string
     */
    public function getCanBeUsedForVariantsString() {
        $CanBeUsedForVariants = _t('Silvercart.NO');
        if ($this->CanBeUsedForVariants) {
            $CanBeUsedForVariants = _t('Silvercart.YES');
        }
        return $CanBeUsedForVariants;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * variants
     *
     * @return string
     */
    public function getCanBeUsedForSingleVariantsString() {
        $CanBeUsedForVariants = _t('Silvercart.NO');
        if ($this->CanBeUsedForSingleVariants) {
            $CanBeUsedForVariants = _t('Silvercart.YES');
        }
        return $CanBeUsedForVariants;
    }
    
    /**
     * Assigns the given values to the assigned values
     *
     * @param ArrayList $valuesToAssign Values to assign
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function assignValues($valuesToAssign) {
        if (is_null($this->assignedValues)) {
            $this->setAssignedValues(new ArrayList());
        }
        if ($valuesToAssign instanceof DataList) {
            $valuesToAssign = new ArrayList($valuesToAssign->toArray());
        }
        $this->assignedValues->merge($valuesToAssign);
    }
    
    /**
     * Returns whether this attribute has assigned values in a product or
     * product group context.
     *
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function hasAssignedValues() {
        $hasAssignedValues = false;
        if (!is_null($this->assignedValues) &&
            $this->assignedValues->Count() > 0) {
            $hasAssignedValues = true;
        }
        return $hasAssignedValues;
    }

    /**
     * Returns the assigned values in relation to a context product
     *
     * @return ArrayList
     */
    public function getAssignedValues() {
        return $this->assignedValues;
    }

    /**
     * Sets the assigned values in relation to a context product
     *
     * @param ArrayList $assignedValues Assigned values
     * 
     * @return void
     */
    public function setAssignedValues($assignedValues) {
        $this->assignedValues = $assignedValues;
    }
    
    /**
     * Returns the not assigned values in relation to a context product
     *
     * @return ArrayList
     */
    public function getUnAssignedValues() {
        return $this->unAssignedValues;
    }

    /**
     * Sets the not assigned values in relation to a context product
     *
     * @param ArrayList $unAssignedValues Not assigned values
     * 
     * @return void
     */
    public function setUnAssignedValues($unAssignedValues) {
        $this->unAssignedValues = $unAssignedValues;
    }
    
    /**
     * Returns whether this attribute has a selected value or not
     * 
     * @return boolean
     */
    public function getHasSelectedValues() {
        $hasSelectedValues  = false;
        $assignedValues     = $this->getAssignedValues();
        if (!is_null($assignedValues)) {
            foreach ($assignedValues as $assignedValue) {
                if ($assignedValue->IsFilterValue()) {
                    $hasSelectedValues = true;
                    break;
                }
            }
        }
        return $hasSelectedValues;
    }
    
}

/**
 * Class to import variants out of the obsolete product variant module.
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2016 pixeltricks GmbH
 * @since 21.09.2016
 * @license see license file in modules root directory
 */
class SilvercartProductAttribute_VariantImporter {
    
    /**
     * Maps the ID of an attribute to an obsolete variant attribute set.
     *
     * @var array
     */
    protected $importAttributeMap = array();
    
    /**
     * Maps the ID of an attribute value to an obsolete variant attribute.
     *
     * @var array
     */
    protected $importAttributeValueMap = array();
    
    /**
     * Maps the ID of an attribute to an obsolete attributed variant attribute set.
     *
     * @var array
     */
    protected $importAttributeSetProductMap = array();
    
    /**
     * Optional MySQL table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Executes the attribute import from the obsolete variant module.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function doImport() {
        $doImport  = false;
        $results   = DB::query('SHOW TABLES');
        $tableName = 'SilvercartProductVariantAttribute';
        
        foreach ($results as $table) {
            if (in_array($tableName, $table) ||
                in_array(strtolower($tableName), $table) ||
                in_array(strtoupper($tableName), $table)) {
                $doImport = true;
                break;
            }
            if (in_array('_obsolete_' . $tableName, $table) ||
                in_array(strtolower('_obsolete_' . $tableName), $table) ||
                in_array(strtoupper('_obsolete_' . $tableName), $table)) {
                $doImport = true;
                $this->tablePrefix = '_obsolete_';
                break;
            }
        }
        if ($doImport) {
            $this->importProductVariantAttributeSets();
            $this->importProductVariantAttributeSets(true, true);
            $this->importProductVariantAttributes();
            $this->importProductVariantAttributes(true, true);
            $this->importProductVariantAttributeRelations(true);
            $this->importProductVariantProductRelations(true);
        }
    }

    /**
     * Imports the product variant modules (obsolete) variants.
     * 
     * @param bool $forTranslations Execute import for translations
     * @param bool $renameTable     Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributeSets($forTranslations = false, $renameTable = false) {
        $whereClause = '"VASL"."Locale" = \'' . i18n::get_locale() . '\'';
        if ($forTranslations) {
            $whereClause = '"VASL"."Locale" != \'' . i18n::get_locale() . '\'';
        }
        $attributeSets = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSet" AS "VAS" LEFT JOIN "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSetLanguage" AS "VASL" ON ("VAS"."ID" = "VASL"."SilvercartProductVariantAttributeSetID") WHERE ' . $whereClause);
        if ($attributeSets->numRecords() > 0) {
            foreach ($attributeSets as $attributeSet) {
                if (array_key_exists($attributeSet['SilvercartProductVariantAttributeSetID'], $this->importAttributeMap)) {
                    $existingAttribute = SilvercartProductAttribute::get()->byID($this->importAttributeMap[$attributeSet['SilvercartProductVariantAttributeSetID']]);
                } else {
                    $existingAttribute = SilvercartProductAttribute::get()->filter('Title', $attributeSet['name'])->first();
                }
                if (is_null($existingAttribute)) {
                    $existingAttribute = new SilvercartProductAttribute();
                    $existingAttribute->Title                        = $attributeSet['name'];
                    $existingAttribute->IsUserInputField             = $attributeSet['type'] == 'userInput';
                    $existingAttribute->UserInputFieldMustBeFilledIn = $attributeSet['mustBeFilledIn'] == '1';
                    $existingAttribute->CanBeUsedForSingleVariants   = true;
                    $existingAttribute->write();
                }
                if ($forTranslations &&
                    !$existingAttribute->hasLanguage($attributeSet['Locale'])) {
                    $translation = new SilvercartProductAttributeLanguage();
                    $translation->Locale                       = $attributeSet['Locale'];
                    $translation->Title                        = $attributeSet['name'];
                    $translation->SilvercartProductAttributeID = $existingAttribute->ID;
                    $translation->write();
                }
                $this->importAttributeMap[$attributeSet['SilvercartProductVariantAttributeSetID']] = $existingAttribute->ID;
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSet" TO "_imported_SilvercartProductVariantAttributeSet"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSetLanguage" TO "_imported_SilvercartProductVariantAttributeSetLanguage"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variants.
     * 
     * @param bool $forTranslations Execute import for translations
     * @param bool $renameTable     Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributes($forTranslations = false, $renameTable = false) {
        $whereClause = '"VAL"."Locale" = \'' . i18n::get_locale() . '\'';
        if ($forTranslations) {
            $whereClause = '"VAL"."Locale" != \'' . i18n::get_locale() . '\'';
        }
        $attributes = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartProductVariantAttributeLanguage" AS "VAL" WHERE ' . $whereClause);
        if ($attributes->numRecords() > 0) {
            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute['SilvercartProductVariantAttributeID'], $this->importAttributeValueMap)) {
                    $existingAttributeValue = SilvercartProductAttributeValue::get()->byID($this->importAttributeValueMap[$attribute['SilvercartProductVariantAttributeID']]);
                } else {
                    $existingAttributeValue = SilvercartProductAttributeValue::get()->filter('Title', $attribute['name'])->first();
                }
                if (is_null($existingAttributeValue)) {
                    $existingAttributeValue = new SilvercartProductAttributeValue();
                    $existingAttributeValue->Title                        = $attribute['name'];
                    $existingAttributeValue->write();
                }
                if ($forTranslations &&
                    !$existingAttributeValue->hasLanguage($attribute['Locale'])) {
                    $translation = new SilvercartProductAttributeLanguage();
                    $translation->Locale                       = $attribute['Locale'];
                    $translation->Title                        = $attribute['name'];
                    $translation->SilvercartProductAttributeID = $existingAttributeValue->ID;
                    $translation->write();
                }
                $this->importAttributeValueMap[$attribute['SilvercartProductVariantAttributeID']] = $existingAttributeValue->ID;
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartProductVariantAttribute" TO "_imported_SilvercartProductVariantAttribute"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartProductVariantAttributeLanguage" TO "_imported_SilvercartProductVariantAttributeLanguage"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant relations.
     * 
     * @param bool $renameTable Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributeRelations($renameTable = false) {
        $attributeRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSet_Attributes" AS "VASA"');
        if ($attributeRelations->numRecords() > 0) {
            foreach ($attributeRelations as $attributeRelation) {
                if (array_key_exists($attributeRelation['SilvercartProductVariantAttributeSetID'], $this->importAttributeMap) &&
                    array_key_exists($attributeRelation['SilvercartProductVariantAttributeID'], $this->importAttributeValueMap)) {
                    $attributeValue = SilvercartProductAttributeValue::get()->byID($this->importAttributeValueMap[$attributeRelation['SilvercartProductVariantAttributeID']]);
                    if ($attributeValue->SilvercartProductAttributeID == 0) {
                        $attributeValue->SilvercartProductAttributeID = $this->importAttributeMap[$attributeRelation['SilvercartProductVariantAttributeSetID']];
                        $attributeValue->write();
                    } elseif ($attributeValue->SilvercartProductAttributeID != $this->importAttributeMap[$attributeRelation['SilvercartProductVariantAttributeSetID']]) {
                        $newAttributeValue = $attributeValue->duplicate();
                        $newAttributeValue->SilvercartProductAttributeID = $this->importAttributeMap[$attributeRelation['SilvercartProductVariantAttributeSetID']];
                        $newAttributeValue->write();
                    }
                }
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartProductVariantAttributeSet_Attributes" TO "_imported_SilvercartProductVariantAttributeSet_Attributes"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant relations.
     * 
     * @param bool $renameTable Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantProductRelations($renameTable = false) {
        $attributeSetProductRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet" AS "AVAS"');
        $products = array();
        if ($attributeSetProductRelations->numRecords() > 0) {
            foreach ($attributeSetProductRelations as $attributeSetProductRelation) {
                $productID  = $attributeSetProductRelation['SilvercartProductID'];
                $attributeD = $this->importAttributeMap[$attributeSetProductRelation['SilvercartProductVariantAttributeSetID']];
                $product    = SilvercartProduct::get()->byID($productID);
                $attribute  = SilvercartProductAttribute::get()->byID($attributeD);
                $this->importAttributeSetProductMap[$attributeSetProductRelation['ID']] = $productID;
                $products[$productID] = $product;
                $product->SilvercartProductAttributes()->add($attribute);
            }
        }
        
        $attributeProductRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet_Attributes" AS "AVASA"');
        if ($attributeProductRelations->numRecords() > 0) {
            foreach ($attributeProductRelations as $attributeProductRelation) {
                if (array_key_exists($attributeProductRelation['SilvercartAttributedVariantAttributeSetID'], $this->importAttributeSetProductMap) &&
                    array_key_exists($attributeProductRelation['SilvercartProductVariantAttributeID'], $this->importAttributeValueMap)) {
                    $productID        = $this->importAttributeSetProductMap[$attributeProductRelation['SilvercartAttributedVariantAttributeSetID']];
                    $attributeValueID = $this->importAttributeValueMap[$attributeProductRelation['SilvercartProductVariantAttributeID']];
                    
                    if (array_key_exists($productID, $products)) {
                        $product = $products[$productID];
                    } else {
                        $product = SilvercartProduct::get()->byID($productID);
                    }
                    $fieldModifiers = $this->getProductVariantAttributeFieldModifiers($attributeProductRelation['SilvercartProductVariantAttributeID'], $attributeProductRelation['SilvercartAttributedVariantAttributeSetID']);
                    $attributeValue = SilvercartProductAttributeValue::get()->byID($attributeValueID);
                    $product->SilvercartProductAttributeValues()->add($attributeValue, array_merge(array(
                        'IsActive'  => $attributeProductRelation['isActive'],
                        'IsDefault' => $attributeProductRelation['isDefault'],
                    ), $fieldModifiers));
                }
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet" TO "_imported_SilvercartAttributedVariantAttributeSet"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet_Attributes" TO "_imported_SilvercartAttributedVariantAttributeSet_Attributes"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant field modifiers.
     * 
     * @param int   $attributeID                     Attribute ID
     * @param array $attributedVariantAttributeSetID Set ID
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function getProductVariantAttributeFieldModifiers($attributeID, $attributedVariantAttributeSetID) {
        $fieldModifiers = array();
        $attributeFieldModifiers = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartProductVariantFieldModifier" AS "PVFM" WHERE "PVFM"."SilvercartProductVariantAttributeID" = ' . $attributeID . ' AND "PVFM"."SilvercartAttributedVariantAttributeSetID" = ' . $attributedVariantAttributeSetID);
        if ($attributeFieldModifiers->numRecords() > 0) {
            foreach ($attributeFieldModifiers as $attributeFieldModifier) {
                if ($attributeFieldModifier['silvercartProductFieldName'] == 'Title') {
                    $fieldModifiers['ModifyTitleAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyTitleValue']  = $attributeFieldModifier['modifierValue'];
                } elseif ($attributeFieldModifier['silvercartProductFieldName'] == 'PriceGrossAmount') {
                    $fieldModifiers['ModifyPriceAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyPriceValue']  = $attributeFieldModifier['modifierValue'];
                } elseif ($attributeFieldModifier['silvercartProductFieldName'] == 'ProductNumberShop') {
                    $fieldModifiers['ModifyProductNumberAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyProductNumberValue']  = $attributeFieldModifier['modifierValue'];
                }
            }
        }
        return $fieldModifiers;
    }
    
}