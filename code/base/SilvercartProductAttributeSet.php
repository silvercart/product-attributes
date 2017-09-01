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
 * Attribute set to collect attributes
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license see license file in modules root directory
 */
class SilvercartProductAttributeSet extends DataObject {
    
    private static $has_many = array(
        'SilvercartProductAttributeSetLanguages'    => 'SilvercartProductAttributeSetLanguage',
    );
    
    private static $many_many = array(
        'SilvercartProductAttributes'  => 'SilvercartProductAttribute',
    );
    
    private static $casting = array(
        'Title'                                         => 'Text',
        'SilvercartProductAttributesAsString'           => 'Text',
        'SilvercartProductAttributesForSummaryFields'   => 'HtmlText',
    );
    
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
                'Title'                                     => _t('SilvercartProductAttributeSet.TITLE'),
                'SilvercartProductAttributeSetLanguages'    => _t('SilvercartProductAttributeSetLanguage.PLURALNAME'),
                'SilvercartProductAttributes'               => _t('SilvercartProductAttribute.PLURALNAME'),
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
     * @since 18.09.2014
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
     * @since 18.09.2014
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
        if ($silvercartProductAttributesArray instanceof SS_Map) {
            $silvercartProductAttributesArray = $silvercartProductAttributesArray->toArray();
        }
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
        if ($silvercartProductAttributesArray instanceof SS_Map) {
            $silvercartProductAttributesArray = $silvercartProductAttributesArray->toArray();
        }
        $silvercartProductAttributesAsString    = implode('<br/>' . PHP_EOL, $silvercartProductAttributesArray);
        return $silvercartProductAttributesAsString;
    }
    
}