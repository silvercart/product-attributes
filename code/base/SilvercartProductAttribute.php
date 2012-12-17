<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttribute extends DataObject {
    
    /**
     * DB attributes
     *
     * @var array
     */
    public static $db = array(
        'CanBeUsedForFilterWidget'  => 'Boolean(1)',
        'CanBeUsedForDataSheet'     => 'Boolean(1)',
        'CanBeUsedForVariants'      => 'Boolean',
    );

    /**
     * has-many relations
     *
     * @var array
     */
    public static $has_many = array(
        'SilvercartProductAttributeLanguages'   => 'SilvercartProductAttributeLanguage',
        'SilvercartProductAttributeValues'      => 'SilvercartProductAttributeValue',
    );
    
    /**
     * belongs-many-many relations
     *
     * @var array
     */
    public static $belongs_many_many = array(
        'SilvercartProducts'                => 'SilvercartProduct',
        'SilvercartProductAttributeSets'    => 'SilvercartProductAttributeSet',
    );

    /**
     * Castings
     *
     * @var array
     */
    public static $casting = array(
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
    public static $indexes = array(
        'CanBeUsedForFilterWidget'  => 'INDEX (CanBeUsedForFilterWidget)',
        'CanBeUsedForDataSheet'     => 'INDEX (CanBeUsedForDataSheet)',
        'CanBeUsedForVariants'      => 'INDEX (CanBeUsedForVariants)',
    );
    
    /**
     * Default sort fields and directions
     *
     * @var string
     */
    public static $default_sort = "`SilvercartProductAttribute`.`CanBeUsedForVariants` DESC, `SilvercartProductAttributeLanguage`.`Title`";
    
    /**
     * Assigned values
     *
     * @var DataObjectSet
     */
    protected $assignedValues = null;
    
    /**
     * Unassigned values
     *
     * @var DataObjectSet
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
                'Title'                                 => _t('SilvercartProductAttribute.TITLE'),
                'PluralTitle'                           => _t('SilvercartProductAttribute.PLURALTITLE'),
                'SilvercartProductAttributeLanguages'   => _t('SilvercartProductAttributeLanguage.PLURALNAME'),
                'SilvercartProductAttributeValues'      => _t('SilvercartProductAttributeValue.PLURALNAME'),
                'SilvercartProducts'                    => _t('SilvercartProduct.PLURALNAME'),
                'SilvercartProductAttributeSets'        => _t('SilvercartProductAttributeSet.PLURALNAME'),
            )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Customized CMS fields
     * 
     * @param array $params Optional params to manuipulate the scaffolding behaviour
     *
     * @return FieldSet the fields for the backend
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 20.06.2012
     */
    public function getCMSFields($params = null) {
        $fields = parent::getCMSFields($params);
        
        $languageFields = SilvercartLanguageHelper::prepareCMSFields($this->getLanguage(true));
        foreach ($languageFields as $languageField) {
            $fields->addFieldToTab('Root.Main', $languageField);
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
            'CanBeUsedForFilterWidgetString'            => $this->fieldLabel('CanBeUsedForFilterWidget'),
            'CanBeUsedForDataSheetString'               => $this->fieldLabel('CanBeUsedForDataSheet'),
            'CanBeUsedForVariantsString'                => $this->fieldLabel('CanBeUsedForVariants'),
            'SilvercartProductAttributeValuesAsString'  => $this->fieldLabel('SilvercartProductAttributeValues'),
        );
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
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
        $silvercartProductAttributeValuesArray      = $this->SilvercartProductAttributeValues()->map();
        $silvercartProductAttributeValuesAsString   = implode(', ', $silvercartProductAttributeValuesArray);
        $silvercartProductAttributeValuesAsString   = stripslashes($silvercartProductAttributeValuesAsString);
        return $silvercartProductAttributeValuesAsString;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * filter widget
     *
     * @return string
     */
    public function getCanBeUsedForFilterWidgetString() {
        $CanBeUsedForFilterWidget = _t('Boolean.NO');
        if ($this->CanBeUsedForFilterWidget) {
            $CanBeUsedForFilterWidget = _t('Boolean.YES');
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
        $CanBeUsedForDataSheet = _t('Boolean.NO');
        if ($this->CanBeUsedForDataSheet) {
            $CanBeUsedForDataSheet = _t('Boolean.YES');
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
        $CanBeUsedForVariants = _t('Boolean.NO');
        if ($this->CanBeUsedForVariants) {
            $CanBeUsedForVariants = _t('Boolean.YES');
        }
        return $CanBeUsedForVariants;
    }
    
    /**
     * Assigns the given values to the assigned values
     *
     * @param DataObjectSet $valuesToAssign Values to assign
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.12.2012
     */
    public function assignValues($valuesToAssign) {
        if (is_null($this->assignedValues)) {
            $this->setAssignedValues(new DataObjectSet());
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
     * @return DataObjectSet
     */
    public function getAssignedValues() {
        return $this->assignedValues;
    }

    /**
     * Sets the assigned values in relation to a context product
     *
     * @param DataObjectSet $assignedValues Assigned values
     * 
     * @return void
     */
    public function setAssignedValues($assignedValues) {
        $this->assignedValues = $assignedValues;
    }
    
    /**
     * Returns the not assigned values in relation to a context product
     *
     * @return DataObjectSet
     */
    public function getUnAssignedValues() {
        return $this->unAssignedValues;
    }

    /**
     * Sets the not assigned values in relation to a context product
     *
     * @param DataObjectSet $unAssignedValues Not assigned values
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