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
 * Value of a product attribute
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeValue extends DataObject {
    
    public static $has_one = array(
        'SilvercartProductAttribute'  => 'SilvercartProductAttribute',
    );
    
    public static $has_many = array(
        'SilvercartProductAttributeValueLanguages'  => 'SilvercartProductAttributeValueLanguage',
    );
    
    public static $belongs_many_many = array(
        'SilvercartProducts'                => 'SilvercartProduct',
    );

    public static $casting = array(
        'Title' => 'Text',
    );
    
    /**
     * getter for the pseudo attribute title
     *
     * @return string the title in the corresponding frontend language 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.05.2012
     */
    public function getTitle() {
        $title = '';
        if ($this->getLanguage()) {
            $title = $this->getLanguage()->Title;
        }
        return $title;
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
                'Title'                                     => _t('SilvercartProductAttributeValue.TITLE'),
                'SilvercartProductAttributeValueLanguages'  => _t('SilvercartProductAttributeValueLanguage.PLURALNAME'),
                'SilvercartProductAttribute'                => _t('SilvercartProductAttribute.SINGULARNAME'),
                'SilvercartProducts'                        => _t('SilvercartProduct.PLURALNAME'),
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
     * @since 04.05.2012
     */
    public function getCMSFields($params = null) {
        $fields = parent::getCMSFields($params);
        
        $languageFields = SilvercartLanguageHelper::prepareCMSFields($this->getLanguage());
        foreach ($languageFields as $languageField) {
            $fields->insertBefore($languageField, 'SilvercartProductAttributeID');
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
            'SilvercartProductAttributeID' => array(
                'title'     => $this->fieldLabel('SilvercartProductAttribute'),
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
            'Title'                             => $this->fieldLabel('Title'),
            'SilvercartProductAttribute.Title'  => $this->fieldLabel('SilvercartProductAttribute'),
        );
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Checks wheter the value is used by the current context filter
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function IsFilterValue() {
        $isFilterValue = false;
        if (Controller::curr()->hasMethod('isFilterValue')) {
            $isFilterValue = Controller::curr()->isFilterValue($this);
        }
        return $isFilterValue;
    }
    
}