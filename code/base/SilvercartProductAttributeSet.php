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
 * Attribute set to collect attributes
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeSet extends DataObject {
    
    public static $db = array(
        'Title'     => 'VarChar(64)',
    );
    
    public static $many_many = array(
        'SilvercartProductAttributes'  => 'SilvercartProductAttribute',
    );
    
    public static $casting = array(
        'SilvercartProductAttributesAsString'           => 'Text',
        'SilvercartProductAttributesForSummaryFields'   => 'HtmlText',
    );

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
                'Title'                         => _t('SilvercartProductAttributeSet.TITLE'),
                'SilvercartProductAttributes'   => _t('SilvercartProductAttribute.PLURALNAME'),
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
            'Title'                                 => $this->fieldLabel('Title'),
            'SilvercartProductAttributesAsString'   => $this->fieldLabel('SilvercartProductAttributes'),
        );
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns the product attributes as a comma separated string
     *
     * @return string
     */
    public function getSilvercartProductAttributesAsString() {
        $silvercartProductAttributesArray       = $this->SilvercartProductAttributes()->map();
        $silvercartProductAttributesAsString    = implode(', ', $silvercartProductAttributesArray);
        return $silvercartProductAttributesAsString;
    }
    
    /**
     * Returns the product attributes as a comma separated string
     *
     * @return string
     */
    public function getSilvercartProductAttributesForSummaryFields() {
        $silvercartProductAttributesArray       = $this->SilvercartProductAttributes()->map();
        $silvercartProductAttributesAsString    = implode('<br/>' . PHP_EOL, $silvercartProductAttributesArray);
        return $silvercartProductAttributesAsString;
    }
    
}