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
 * Value of a product attribute
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license see license file in modules root directory
 */
class SilvercartProductAttributeValue extends DataObject {
    
    private static $has_one = array(
        'SilvercartProductAttribute' => 'SilvercartProductAttribute',
        'Image'                      => 'Image',
    );
    
    private static $has_many = array(
        'SilvercartProductAttributeValueLanguages'  => 'SilvercartProductAttributeValueLanguage',
    );
    
    private static $belongs_many_many = array(
        'SilvercartProducts'                => 'SilvercartProduct',
    );

    private static $casting = array(
        'Title' => 'Text',
    );
    
    private static $default_sort = '"SilvercartProductAttributeValueLanguage"."Title"';
    
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
                'Image'                                     => SilvercartImage::singleton()->singular_name(),
            )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList the fields for the backend
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function getCMSFields() {
        $fields = SilvercartDataObject::getCMSFields($this);
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
            'SilvercartProductAttributeValueLanguages.Title' => array(
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
    
    /**
     * Returns true to use buttons to toggle IsActive state of a product related
     * attribute value used as variant.
     * 
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function SubObjectHasIsActive() {
        return true;
    }
    
    /**
     * Returns true to use buttons to toggle IsDefault state of a product related
     * attribute value used as variant.
     * 
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function SubObjectHasIsDefault() {
        return true;
    }
    
}