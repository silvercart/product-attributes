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
        'CanBeUsedForFilterWidget'  => 'Boolean(1)',
        'CanBeUsedForDataSheet'     => 'Boolean(1)',
        'CanBeUsedForVariants'      => 'Boolean',
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
    );
    
    /**
     * DB indexes
     * 
     * @var array 
     */
    private static $indexes = array(
        'CanBeUsedForFilterWidget'  => '("CanBeUsedForFilterWidget")',
        'CanBeUsedForDataSheet'     => '("CanBeUsedForDataSheet")',
        'CanBeUsedForVariants'      => '("CanBeUsedForVariants")',
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
                'CanBeUsedForFilterWidgetShort'         => _t('SilvercartProductAttribute.CanBeUsedForFilterWidgetShort'),
                'CanBeUsedForDataSheetShort'            => _t('SilvercartProductAttribute.CanBeUsedForDataSheetShort'),
                'CanBeUsedForVariantsShort'             => _t('SilvercartProductAttribute.CanBeUsedForVariantsShort'),
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