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
class SilvercartProductAttributeProduct extends DataObjectDecorator {

    /**
     * Set of variants related with this product
     *
     * @var DataObjectSet 
     */
    protected $variants = null;
    
    /**
     * A set of the products attributes with the related values
     *
     * @var DataObjectSet 
     */
    protected $attributesWithValues = null;
    
    
    /**
     * A request cached map of attribute value IDs
     *
     * @var array
     */
    protected $relatedAttributeValueMap = null;
    
    /**
     * A request cached list of attributed values
     *
     * @var array
     */
    protected $attributedValues = array();
    
    /**
     * Adds som extra data model fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function extraStatics() {
        return array(
            'many_many'  => array(
                'SilvercartProductAttributes'       => 'SilvercartProductAttribute',
                'SilvercartProductAttributeValues'  => 'SilvercartProductAttributeValue',
            ),
        );
    }
    
    /**
     * Updates the CMS fields
     *
     * @param FieldSet &$fields Fields to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateCMSFields(FieldSet &$fields) {
        $owner  = $this->owner;
        if ($owner->ID > 0) {
            $fields->removeByName('SilvercartProductAttributes');
            $fields->removeByName('SilvercartProductAttributeValues');
            $fields->findOrMakeTab('Root.SilvercartProductAttributes', _t('SilvercartProductAttribute.TABNAME'));
            $attributeField = new SilvercartProductAttributeTableListField($owner, 'SilvercartProductAttributes');
            $fields->addFieldToTab('Root.SilvercartProductAttributes', $attributeField);
            
            if ($this->CanBeUsedAsVariant()) {
                if ($this->hasVariants()) {
                    $slaveProductsLabel = new HeaderField('SilvercartSlaveProductsLabel', $owner->fieldLabel('SilvercartSlaveProducts'));
                    $slaveProductsField = new SilvercartProductAttributeVariantTableListField(
                            $this->owner,
                            'SilvercartSlaveProducts'
                    );
                    $slaveProductsField->setPermissions(array());
                    $fields->addFieldToTab('Root.SilvercartProductAttributes', $slaveProductsLabel);
                    $fields->addFieldToTab('Root.SilvercartProductAttributes', $slaveProductsField);
                }
                if (!$this->isMasterProduct()) {
                    $masterProductField = new SilvercartTextAutoCompleteField(
                            $owner,
                            'SilvercartMasterProductID',
                            $owner->fieldLabel('SilvercartMasterProduct'),
                            'SilvercartProduct.ProductNumberShop'
                    );
                    $fields->addFieldToTab('Root.SilvercartProductAttributes', $masterProductField);
                }
            }
        }
    }
    
    /**
     * Updates the field labels
     *
     * @param array &$labels Labels to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
                $labels,
                array(
                    'SilvercartMasterProduct'           => _t('SilvercartProductAttributeProduct.MASTER_PRODUCT'),
                    'SilvercartProductAttributes'       => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTES'),
                    'SilvercartProductAttributeValues'  => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTE_VALUES'),
                    'SilvercartSlaveProducts'           => _t('SilvercartProductAttributeProduct.SLAVE_PRODUCTS'),
                    'SilvercartProductAttribute'        => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTE'),
                    'SilvercartProductAttributeValue'   => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTE_VALUE'),
                )
        );
    }
    
    /**
     * Inherits the short description of the master product if not set
     * 
     * @param string &$shortDescription Original short description
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function updateShortDescription(&$shortDescription) {
        if (empty($shortDescription) &&
            $this->isSlaveProduct()) {
            $shortDescription = $this->owner->SilvercartMasterProduct()->ShortDescription;
        }
    }
    
    /**
     * Inherits the long description of the master product if not set
     * 
     * @param string &$longDescription Original long description
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function updateLongDescription(&$longDescription) {
        if (empty($longDescription) &&
            $this->isSlaveProduct()) {
            $longDescription = $this->owner->SilvercartMasterProduct()->LongDescription;
        }
    }

    /**
     * Returns the products attributes with related values
     * 
     * @return DataObjectSet
     */
    public function getAttributesWithValues() {
        if (is_null($this->attributesWithValues)) {
            $this->attributesWithValues = new DataObjectSet();
            foreach ($this->owner->SilvercartProductAttributes() as $attribute) {
                $attributedValues = $this->getAttributedValuesFor($attribute);
                if ($attributedValues->Count() > 0) {
                    $this->attributesWithValues->push(
                            new ArrayData(
                                    array(
                                        'Attribute' => $attribute,
                                        'Values'    => $attributedValues,
                                    )
                            )
                    );
                }
            }
        }
        return $this->attributesWithValues;
    }

    /**
     * Returns the products attributed values for the given attribute
     * 
     * @param SilvercartProductAttribute $attribute Attribute to get values for
     * 
     * @return DataObjectSet
     */
    public function getAttributedValuesFor($attribute) {
        if (!array_key_exists($attribute->ID, $this->attributedValues)) {
            $assignedValueIDs   = array();
            if (is_null($this->relatedAttributeValueMap)) {
                $this->relatedAttributeValueMap = $this->owner->SilvercartProductAttributeValues()->map('ID', 'ID');
            }
            $attributeMap = $attribute->SilvercartProductAttributeValues()->map('ID', 'ID');

            foreach ($this->relatedAttributeValueMap as $attributeValueID) {
                if (array_key_exists($attributeValueID, $attributeMap)) {
                    $assignedValueIDs[] = $attributeValueID;
                }
            }
            if (count($assignedValueIDs) > 0) {
                $attributedValues = DataObject::get(
                        'SilvercartProductAttributeValue',
                        sprintf(
                                "SilvercartProductAttributeValue.ID IN (%s)",
                                implode(',', $assignedValueIDs)
                        )
                );
            } else {
                $attributedValues = new DataObjectSet();
            }
            $this->attributedValues[$attribute->ID] = $attributedValues;
        }
        return $this->attributedValues[$attribute->ID];
    }

    /**
     * Returns whether this product has variants or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function hasVariants() {
        $hasVariants    = false;
        $variants       = $this->getVariants();
        if (!is_null($variants) &&
            $variants->Count() > 0) {
            $hasVariants = true;
        }
        return $hasVariants;
    }
    
    /**
     * Returns the variants for the given Attribute ID
     * 
     * @param int $attributeID Attribute ID to get variants for
     * 
     * @return DataObjectSet
     */
    public function getVariantsFor($attributeID) {
        $matchedVariants            = new DataObjectSet();
        $matchingAttributeValues    = new DataObjectSet();
        $variants                   = $this->getVariants();
        $variantAttributes          = $this->getVariantAttributes();
        $variantAttributes->remove($variantAttributes->find('ID', $attributeID));
        foreach ($variantAttributes as $variantAttribute) {
            $matchingAttributeValues->merge($this->owner->getAttributedValuesFor($variantAttribute));
        }
        
        foreach ($variants as $variant) {
            if ($variant->SilvercartProductAttributes()->find('ID', $attributeID)) {
                $attributeValueMatches = array();
                foreach ($matchingAttributeValues as $matchingAttributeValue) {
                    if ($variant->SilvercartProductAttributeValues()->find('ID', $matchingAttributeValue->ID)) {
                        $attributeValueMatches[] = true;
                    }
                }
                if (count($attributeValueMatches) >= count($matchingAttributeValues)) {
                    $matchedVariants->push($variant);
                }
            }
        }
        return $matchedVariants;
    }
    
    /**
     * Returns the products variant matching with the given attribute value IDs
     * 
     * @param array $attributeValueIDs IDs of the attribute values to match against
     * 
     * @return SilvercartProduct
     */
    public function getVariantBy($attributeValueIDs) {
        $matchedVariant = null;
        $variants       = $this->getVariants();
        foreach ($variants as $variant) {
            $matched = array();
            foreach ($attributeValueIDs as $attributeValueID) {
                if ($variant->SilvercartProductAttributeValues()->find('ID', $attributeValueID)) {
                    $matched[] = true;
                }
            }
            if (count($matched) == count($attributeValueIDs)) {
                $matchedVariant = $variant;
                break;
            }
        }
        return $matchedVariant;
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return DataObjectSet
     */
    public function getVariantAttributes() {
        $variantAttributes  = new DataObjectSet();
        $attributes         = $this->owner->SilvercartProductAttributes();
        $groupedAttributes  = $attributes->groupBy('CanBeUsedForVariants');
        if (array_key_exists(1, $groupedAttributes)) {
            $variantAttributes = $groupedAttributes[1];
        }
        return $variantAttributes;
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return DataObjectSet
     */
    public function getVariantAttributeValues() {
        $variantAttributeValues = new DataObjectSet();
        $variantAttributes      = $this->getVariantAttributes();
        foreach ($variantAttributes as $variantAttribute) {
            $variantAttributeValue = $this->owner->SilvercartProductAttributeValues()->find('SilvercartProductAttributeID', $variantAttribute->ID);
            if ($variantAttributeValue) {
                $variantAttributeValues->push($variantAttributeValue);
            }
        }
        return $variantAttributeValues;
    }

    /**
     * Returns the variants of this product
     * 
     * @return DataObjectSet
     */
    public function getVariants() {
        if (is_null($this->variants) &&
            $this->isVariant()) {
            if ($this->isSlaveProduct()) {
                $master = $this->owner->SilvercartMasterProduct();
            } else {
                $master = $this->owner;
            }
            $variants = $master->getSlaveProducts();
            if ($this->isSlaveProduct()) {
                $variants->remove($variants->find('ID', $this->owner->ID));
                $variants->push($master);
                $variants->removeDuplicates();
            }
            $groupedVariants = $variants->groupBy('isActive');
            if (array_key_exists(1, $groupedVariants)) {
                $this->variants = $groupedVariants[1];
            }
        }
        return $this->variants;
    }
    
    /**
     * Sets the variants for this product
     * 
     * @param DataObjectSet $variants Variants to use
     * 
     * @return void
     */
    public function setVariants($variants) {
        $this->variants = $variants;
    }


    /**
     * Returns whether this product is a variant of another product
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isVariant() {
        $isVariant = false;
        if ($this->CanBeUsedAsVariant() &&
            ($this->isMasterProduct() ||
             $this->isSlaveProduct())) {
            $isVariant = true;
        }
        return $isVariant;
    }

        /**
     * Returns whether this product can be used as variant or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function CanBeUsedAsVariant() {
        $canBeUsedAsVariant         = false;
        $owner                      = $this->owner;
        $productAttributes          = $owner->SilvercartProductAttributes();
        $groupedProductAttributes   = $productAttributes->groupBy('CanBeUsedForVariants');
        if (array_key_exists(1, $groupedProductAttributes)) {
            $canBeUsedAsVariant = true;
        }
        return $canBeUsedAsVariant;
    }


    /**
     * Returns this products slave products if exists
     * 
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function getSlaveProducts() {
        $owner  = $this->owner;
        $slaves = DataObject::get(
                'SilvercartProduct',
                sprintf(
                        "`SilvercartMasterProductID` = '%s'",
                        $owner->ID
                )
        );
        return $slaves;
    }
    
    /**
     * Returns whether this is a master product or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isMasterProduct() {
        $isMasterProduct    = false;
        $slaves             = $this->getSlaveProducts();
        if ($slaves &&
            $slaves->Count() > 0) {
            $isMasterProduct = true;
        }
        return $isMasterProduct;
    }
    
    /**
     * Returns whether this is a slave product or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isSlaveProduct() {
        $isSlaveProduct = false;
        $owner          = $this->owner;
        if ($owner->SilvercartMasterProductID > 0) {
            $isSlaveProduct = true;
        }
        return $isSlaveProduct;
    }
    
    /**
     * Returns the form fields for the choice of a products variant to use with 
     * CustomHtmlForm
     * 
     * @return array
     */
    public function getVariantFormFields() {
        $product    = $this->owner;
        $fieldGroup = array();
        if ($product->hasVariants()) {
            $attributes         = $product->getVariantAttributes();
            $fieldModifierNotes = array();

            foreach ($attributes as $attribute) {
                $values         = array();
                $selectedValue  = 0;
                $variants       = $product->getVariantsFor($attribute->ID);

                $attributedValues = $product->getAttributedValuesFor($attribute);
                if ($attributedValues->Count() > 0) {
                    $selectedValue  = $attributedValues->First()->ID;
                }
                
                foreach ($variants as $variant) {
                    $addition = '';
                    if ($variant->getPrice()->getAmount() > $product->getPrice()->getAmount()) {
                        $additionMoney = new Money();
                        $additionMoney->setAmount($variant->getPrice()->getAmount() - $product->getPrice()->getAmount());
                        $additionMoney->setCurrency($product->getPrice()->getCurrency());
                        $addition = '+' . $additionMoney->Nice();
                    } elseif ($variant->getPrice()->getAmount() < $product->getPrice()->getAmount()) {
                        $additionMoney = new Money();
                        $additionMoney->setAmount($product->getPrice()->getAmount() - $variant->getPrice()->getAmount());
                        $additionMoney->setCurrency($product->getPrice()->getCurrency());
                        $addition = '-' . $additionMoney->Nice();
                    }
                    $attributedValues->merge($variant->getAttributedValuesFor($attribute));
                    $variantMap = $variant->getAttributedValuesFor($attribute)->map('ID','ID');
                    foreach ($variantMap as $ID) {
                        if ($ID != $selectedValue) {
                            $fieldModifierNotes[$ID] = $addition;
                        }
                    }
                }

                foreach ($attributedValues as $attributedValue) {
                    $attributeName      = $attributedValue->Title;
                    $fieldModifierNote  = '';
                    if (array_key_exists($attributedValue->ID, $fieldModifierNotes)) {
                        $fieldModifierNote = $fieldModifierNotes[$attributedValue->ID];
                    }

                    if (!empty($fieldModifierNote)) {
                        $attributeName .= ' (' . $fieldModifierNote . ')';
                    }

                    $values[$attributedValue->ID] = $attributeName;
                }

                if (count($values) > 1) {
                    $fieldType = 'SilvercartProductAttributeDropdownField';

                    if (!empty($attribute->useCustomFormField)) {
                        $fieldType = $attribute->useCustomFormField;
                    }

                    $checkRequirements = array();

                    $fieldGroup['SilvercartProductAttribute' . $attribute->ID] = array(
                        'type'              => $fieldType,
                        'title'             => $attribute->Title,
                        'value'             => $values,
                        'selectedValue'     => $selectedValue,
                        'silvercartProduct' => $product,
                        'checkRequirements' => $checkRequirements
                    );
                }
            }
        }
        return $fieldGroup;
    }
    
}

/**
 * The SilvercartProductAttributeProduct_RecordController record controller.
 *
 * @package Silvercart
 * @subpackage Backend
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 14.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeProduct_RecordController extends DataObjectDecorator {
    
    /**
     * Adds the abillity to execute additional actions to the model admin's
     * action handling.
     *
     * @param SS_HTTPRequest $request The request object
     * 
     * @return mixed
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function handleAction(SS_HTTPRequest $request) {
        $allRequestParams = $request->allParams();
        
        if (array_key_exists('ID', $allRequestParams)) {
            $action = str_replace('action_', '', $allRequestParams['ID']);
        }
        if (array_key_exists('OtherID', $allRequestParams)) {
            $otherId = $allRequestParams['OtherID'];
        }
        
        switch ($action) {
            case 'doSPAPAddUnAssignedAttribute':
                return $this->doAddUnAssignedAttribute($request, $otherId);
                break;
            case 'doSPAPRemoveAssignedAttribute':
                return $this->doRemoveAssignedAttribute($request, $otherId);
                break;
            case 'doSPAPAddUnAssignedValue':
                return $this->doAddUnAssignedValue($request, $otherId);
                break;
            case 'doSPAPRemoveAssignedValue':
                return $this->doRemoveAssignedValue($request, $otherId);
                break;
            default:
                return false;
        }
    }
    
    /**
     * Adds an unassigned attribute set to a product.
     *
     * @param SS_HTTPRequest $request     The request object
     * @param int            $attributeID The ID of the attributeset
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doAddUnAssignedAttribute(SS_HTTPRequest $request, $attributeID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attribute          = DataObject::get_by_id('SilvercartProductAttribute', $attributeID);
        
        if ($attribute) {
            if (!$silvercartProduct->SilvercartProductAttributes()->find('ID', $attribute->ID)) {
                $silvercartProduct->SilvercartProductAttributes()->add($attribute);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attribute->ID);
    }
    
    /**
     * Removes an assigned attribute set to a product.
     *
     * @param SS_HTTPRequest $request     The request object
     * @param int            $attributeID The ID of the attributed attribute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doRemoveAssignedAttribute(SS_HTTPRequest $request, $attributeID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attribute          = DataObject::get_by_id('SilvercartProductAttribute', $attributeID);
        
        if ($attribute) {
            $silvercartProduct->SilvercartProductAttributes()->remove($attribute);
            foreach ($silvercartProduct->SilvercartProductAttributeValues() as $value) {
                if ($value->SilvercartProductAttributeID == $attribute->ID) {
                    $silvercartProduct->SilvercartProductAttributeValues()->remove($value);
                    $silvercartProduct->write();
                }
            }
        }
        
        return $this->returnTableFieldForAjaxRequests();
    }
    
    /**
     * Adds an unassigned attribute value to a product.
     *
     * @param SS_HTTPRequest $request          The request object
     * @param int            $attributeValueID The ID of the attribute value
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doAddUnAssignedValue(SS_HTTPRequest $request, $attributeValueID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attributeValue     = DataObject::get_by_id('SilvercartProductAttributeValue', $attributeValueID);
        
        if ($attributeValue) {
            if (!$silvercartProduct->SilvercartProductAttributeValues()->find('ID', $attributeValue->ID)) {
                $silvercartProduct->SilvercartProductAttributeValues()->add($attributeValue);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attributeValue->SilvercartProductAttribute()->ID);
    }
    
    /**
     * Adds an unassigned attribute value to a product.
     *
     * @param SS_HTTPRequest $request          The request object
     * @param int            $attributeValueID The ID of the attribute value
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doRemoveAssignedValue(SS_HTTPRequest $request, $attributeValueID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attributeValue     = DataObject::get_by_id('SilvercartProductAttributeValue', $attributeValueID);
        
        if ($attributeValue) {
            if ($silvercartProduct->SilvercartProductAttributeValues()->find('ID', $attributeValue->ID)) {
                $silvercartProduct->SilvercartProductAttributeValues()->remove($attributeValue);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attributeValue->SilvercartProductAttribute()->ID);
    }
    
    /**
     * Returns the SilvercartProductAttributeTableListField for ajax requests as 
     * HTML code.
     *
     * @param int $activeAttributeID The ID of the active attribute
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    protected function returnTableFieldForAjaxRequests($activeAttributeID = null) {
        $data = SilvercartProductAttributeTableListField::getFieldData($this->owner->currentRecord, $activeAttributeID);
        return $this->owner->customise($data)->renderWith('SilvercartProductAttributeTableListField');
    }
}
