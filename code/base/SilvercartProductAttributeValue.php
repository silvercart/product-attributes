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
    
    private static $db = array(
        'DefaultModifyTitleAction'         => "enum(',add,setTo','')",
        'DefaultModifyTitleValue'          => "Varchar(256)",
        'DefaultModifyPriceAction'         => "enum(',add,subtract,setTo','')",
        'DefaultModifyPriceValue'          => "Varchar(10)",
        'DefaultModifyProductNumberAction' => "enum(',add,setTo','')",
        'DefaultModifyProductNumberValue'  => "Varchar(50)",
    );
    
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
        'Title'                          => 'Text',
        'FinalModifyTitleAction'         => 'Text',
        'FinalModifyTitleValue'          => 'Text',
        'FinalModifyPriceAction'         => 'Text',
        'FinalModifyPriceValue'          => 'Text',
        'FinalModifyProductNumberAction' => 'Text',
        'FinalModifyProductNumberValue'  => 'Text',
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
                'Title'                                    => _t('SilvercartProductAttributeValue.TITLE'),
                'SilvercartProductAttributeValueLanguages' => _t('SilvercartProductAttributeValueLanguage.PLURALNAME'),
                'SilvercartProductAttribute'               => _t('SilvercartProductAttribute.SINGULARNAME'),
                'SilvercartProducts'                       => _t('SilvercartProduct.PLURALNAME'),
                'Image'                                    => SilvercartImage::singleton()->singular_name(),
                'Default'                                  => _t('SilvercartProductAttributeValue.Default', "default"),
                'DefaultModifyDesc'                        => _t('SilvercartProductAttributeValue.DefaultModifyDesc', "Will be used as default for related products. Can be overwritten individually for each product."),
                'DefaultModifyAction'                      => _t('SilvercartProductAttributeValue.DefaultModifyAction', "Action"),
                'DefaultModifyActionNone'                  => _t('SilvercartProductAttributeValue.DefaultModifyActionNone', "-none-"),
                'DefaultModifyValue'                       => _t('SilvercartProductAttributeValue.DefaultModifyValue', "Value"),
                'DefaultModifyPrice'                       => _t('SilvercartProductAttributeValue.DefaultModifyPrice', "Default product price modification"),
                'DefaultModifyPriceAction'                 => _t('SilvercartProductAttributeValue.DefaultModifyAction', "Action"),
                'DefaultModifyPriceActionAdd'              => _t('SilvercartProductAttributeValue.DefaultModifyActionAdd', "Add"),
                'DefaultModifyPriceActionSetTo'            => _t('SilvercartProductAttributeValue.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyPriceActionSubtract'         => _t('SilvercartProductAttributeValue.DefaultModifyActionSubtract', "Subtract"),
                'DefaultModifyPriceValue'                  => _t('SilvercartProductAttributeValue.DefaultModifyValue', "Value"),
                'DefaultModifyProductNumber'               => _t('SilvercartProductAttributeValue.DefaultModifyProductNumber', "Default product number modification"),
                'DefaultModifyProductNumberAction'         => _t('SilvercartProductAttributeValue.DefaultModifyAction', "Action"),
                'DefaultModifyProductNumberActionAdd'      => _t('SilvercartProductAttributeValue.DefaultModifyActionAdd', "Add"),
                'DefaultModifyProductNumberActionSetTo'    => _t('SilvercartProductAttributeValue.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyProductNumberValue'          => _t('SilvercartProductAttributeValue.DefaultModifyValue', "Value"),
                'DefaultModifyTitle'                       => _t('SilvercartProductAttributeValue.DefaultModifyTitle', "Default product title modification"),
                'DefaultModifyTitleAction'                 => _t('SilvercartProductAttributeValue.DefaultModifyAction', "Action"),
                'DefaultModifyTitleActionAdd'              => _t('SilvercartProductAttributeValue.DefaultModifyActionAdd', "Add"),
                'DefaultModifyTitleActionSetTo'            => _t('SilvercartProductAttributeValue.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyTitleValue'                  => _t('SilvercartProductAttributeValue.DefaultModifyValue', "Value"),
                'ModifyPrice'                              => _t('SilvercartProductAttributeValue.ModifyPrice', "Modify product price"),
                'ModifyProductNumber'                      => _t('SilvercartProductAttributeValue.ModifyProductNumber', "Modify product number"),
                'ModifyTitle'                              => _t('SilvercartProductAttributeValue.ModifyTitle', "Modify product title"),
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
        if ($this->SilvercartProductAttribute()->CanBeUsedForSingleVariants) {
            $fields->dataFieldByName('DefaultModifyPriceValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            $fields->dataFieldByName('DefaultModifyProductNumberValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            $fields->dataFieldByName('DefaultModifyTitleValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            
            $fields->dataFieldByName('DefaultModifyPriceAction')->setSource(SilvercartTools::enum_i18n_labels($this, 'DefaultModifyPriceAction', $this->fieldLabel('DefaultModifyActionNone')));
            $fields->dataFieldByName('DefaultModifyProductNumberAction')->setSource(SilvercartTools::enum_i18n_labels($this, 'DefaultModifyProductNumberAction', $this->fieldLabel('DefaultModifyActionNone')));
            $fields->dataFieldByName('DefaultModifyTitleAction')->setSource(SilvercartTools::enum_i18n_labels($this, 'DefaultModifyTitleAction', $this->fieldLabel('DefaultModifyActionNone')));
            
            $priceField = SilvercartFieldGroup::create('DefaultModifyPrice', $this->fieldLabel('DefaultModifyPrice'), $fields);
            $priceField->push($fields->dataFieldByName('DefaultModifyPriceAction'));
            $priceField->push($fields->dataFieldByName('DefaultModifyPriceValue'));
            
            $productNumberField = SilvercartFieldGroup::create('DefaultModifyProductNumber', $this->fieldLabel('DefaultModifyProductNumber'), $fields);
            $productNumberField->push($fields->dataFieldByName('DefaultModifyProductNumberAction'));
            $productNumberField->push($fields->dataFieldByName('DefaultModifyProductNumberValue'));
            
            $titleField = SilvercartFieldGroup::create('DefaultModifyTitle', $this->fieldLabel('DefaultModifyTitle'), $fields);
            $titleField->push($fields->dataFieldByName('DefaultModifyTitleAction'));
            $titleField->push($fields->dataFieldByName('DefaultModifyTitleValue'));
            
            $fields->addFieldToTab('Root.Main', $priceField);
            $fields->addFieldToTab('Root.Main', $productNumberField);
            $fields->addFieldToTab('Root.Main', $titleField);
        } else {
            $fields->removeByName('DefaultModifyPriceAction');
            $fields->removeByName('DefaultModifyPriceValue');
            $fields->removeByName('DefaultModifyProductNumberAction');
            $fields->removeByName('DefaultModifyProductNumberValue');
            $fields->removeByName('DefaultModifyTitleAction');
            $fields->removeByName('DefaultModifyTitleValue');
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
    
    /**
     * Returns the abbreviation for the given action.
     * 
     * @param string $action Action
     * 
     * @return string
     */
    public function getActionAbbreviation($action) {
        $abbr = '';
        switch ($action) {
            case 'add':
                $abbr = '+';
                break;
            case 'setTo':
                $abbr = '=';
                break;
            case 'subtract':
                $abbr = '-';
                break;
            default:
                break;
        }
        return $abbr;
    }
    
    /**
     * Returns the default modification text.
     * 
     * @param string $action Action
     * 
     * @return string
     */
    public function getDefaultModificationText($text, $action) {
        $defaultText = '(' . $this->fieldLabel('Default') . ': ' . $this->getActionAbbreviation($action) . $text . ')';
        return $defaultText;
    }
    
    /**
     * Returns whether the title has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyTitle() {
        return !empty($this->DefaultModifyTitleAction) && !empty($this->DefaultModifyTitleValue);
    }
    
    /**
     * Returns the default title modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyTitleText() {
        $text = '';
        if ($this->DefaultModifyTitle()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyTitleValue, $this->DefaultModifyTitleAction);
        }
        return $text;
    }
    
    /**
     * Returns whether the price has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyPrice() {
        return !empty($this->DefaultModifyPriceAction) && !empty($this->DefaultModifyPriceValue);
    }
    
    /**
     * Returns the default price modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyPriceText() {
        $text = '';
        if ($this->DefaultModifyPrice()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyPriceValue, $this->DefaultModifyPriceAction);
        }
        return $text;
    }
    
    /**
     * Returns whether the product number has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyProductNumber() {
        return !empty($this->DefaultModifyProductNumberAction) && !empty($this->DefaultModifyProductNumberValue);
    }
    
    /**
     * Returns the default product number modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyProductNumberText() {
        $text = '';
        if ($this->DefaultModifyProductNumber()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyProductNumberValue, $this->DefaultModifyProductNumberAction);
        }
        return $text;
    }
    
    /**
     * Returns the final price modification action.
     * 
     * @return string
     */
    public function getFinalModifyPriceAction() {
        if (!empty($this->ModifyPriceAction)) {
            return $this->ModifyPriceAction;
        }
        return $this->DefaultModifyPriceAction;
    }
    
    /**
     * Returns the final price modification value.
     * 
     * @return string
     */
    public function getFinalModifyPriceValue() {
        if (!empty($this->ModifyPriceValue) &&
            !empty($this->ModifyPriceAction)) {
            return $this->ModifyPriceValue;
        }
        return $this->DefaultModifyPriceValue;
    }
    
    /**
     * Returns the final product number modification action.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberAction() {
        if (!empty($this->ModifyProductNumberAction)) {
            return $this->ModifyProductNumberAction;
        }
        return $this->DefaultModifyProductNumberAction;
    }
    
    /**
     * Returns the final product number modification value.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberValue() {
        if (!empty($this->ModifyProductNumberValue) &&
            !empty($this->ModifyProductNumberAction)) {
            return $this->ModifyProductNumberValue;
        }
        return $this->DefaultModifyProductNumberValue;
    }
    
    /**
     * Returns the final title modification action.
     * 
     * @return string
     */
    public function getFinalModifyTitleAction() {
        if (!empty($this->ModifyTitleAction)) {
            return $this->ModifyTitleAction;
        }
        return $this->DefaultModifyTitleAction;
    }
    
    /**
     * Returns the final title modification value.
     * 
     * @return string
     */
    public function getFinalModifyTitleValue() {
        if (!empty($this->ModifyTitleValue) &&
            !empty($this->ModifyTitleAction)) {
            return $this->ModifyTitleValue;
        }
        return $this->DefaultModifyTitleValue;
    }
    
}