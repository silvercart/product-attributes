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
    
    public static $db = array(
        'Title'     => 'VarChar(64)',
    );
    
    public static $has_many = array(
        'SilvercartProductAttributeValues'  => 'SilvercartProductAttributeValue',
    );
    
    public static $belongs_many_many = array(
        'SilvercartProducts'                => 'SilvercartProduct',
        'SilvercartProductAttributeSets'    => 'SilvercartProductAttributeSet',
    );

    public static $casting = array(
        'SilvercartProductAttributeSetsAsString'    => 'Text',
        'SilvercartProductAttributeValuesAsString'  => 'Text',
    );
    
    public static $default_sort = "`Title`";
    
    protected $assignedValues = null;
    
    protected $unAssignedValues = null;

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
                'Title'                             => _t('SilvercartProductAttribute.TITLE'),
                'SilvercartProductAttributeValues'  => _t('SilvercartProductAttributeValue.PLURALNAME'),
            )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
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
        if (_t($this->ClassName . '.PLURALNAME')) {
            self::$plural_name = _t($this->ClassName . '.PLURALNAME');
        } else {
            self::$plural_name = parent::plural_name();
        }
        return self::$plural_name;
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
            'Title' => array(
                'title'     => $this->fieldLabel('Title'),
                'filter'    => 'PartialMatchFilter'
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
        if (_t($this->ClassName . '.SINGULARNAME')) {
            self::$singular_name = _t($this->ClassName . 'SINGULARNAME');
        } else {
            self::$singular_name = parent::singular_name();
        }
        return self::$singular_name;
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
     * Assigns the given values to the assigned values
     *
     * @param DataObjectSet $valuesToAssign Values to assign
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function assignValues($valuesToAssign) {
        if (is_null($this->assignedValues)) {
            $this->setAssignedValues(new DataObjectSet());
        }
        foreach ($valuesToAssign as $value) {
            if ($value->SilvercartProductAttribute()->ID == $this->ID) {
                if ($this->assignedValues->find('ID', $value->ID)) {
                    continue;
                }
                $this->assignedValues->push($value);
            }
        }
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
    
}