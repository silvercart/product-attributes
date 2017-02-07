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
class SilvercartProductAttributeProduct extends DataExtension {
    
    /**
     * Many to many relations
     *
     * @var array
     */
    private static $many_many = array(
        'SilvercartProductAttributes'       => 'SilvercartProductAttribute',
        'SilvercartProductAttributeValues'  => 'SilvercartProductAttributeValue',
    );
    
    /**
     * Extra fields for many to many relations.
     *
     * @var array
     */
    private static $many_many_extraFields = array(
        'SilvercartProductAttributeValues' => array(
            'IsActive'  => 'Boolean',
            'IsDefault' => 'Boolean',
            'ModifyTitleAction'         => "enum(',add,setTo','')",
            'ModifyTitleValue'          => "Varchar(256)",
            'ModifyPriceAction'         => "enum(',add,subtract,setTo','')",
            'ModifyPriceValue'          => "Varchar(10)",
            'ModifyProductNumberAction' => "enum(',add,setTo','')",
            'ModifyProductNumberValue'  => "Varchar(50)",
        ),
    );

    /**
     * Set of variants related with this product
     *
     * @var ArrayList 
     */
    protected $variants = null;
    
    /**
     * A set of the products attributes with the related values
     *
     * @var ArrayList 
     */
    protected $attributesWithValues = null;
    
    
    /**
     * A request cached map of attribute value IDs
     *
     * @var array
     */
    protected $relatedAttributeValueMap = null;
    
    /**
     * Indicator whether updateCMSFields is already called
     *
     * @var bool
     */
    protected $updateCMSFieldsIsCalled = false;
    
    /**
     * Updates the CMS fields
     *
     * @param FieldList $fields Fields to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateCMSFields(FieldList $fields) {
        if (!$this->updateCMSFieldsIsCalled) {
            $this->updateCMSFieldsIsCalled = true;
            $fields->removeByName('SilvercartProductAttributeValues');
            if ($this->owner->exists()) {
                $attributeField = $fields->dataFieldByName('SilvercartProductAttributes');
                /* @var $attributeField GridField */
                $subObjectComponent = new SilvercartProductAttributeGridFieldSubObjectHandler($this->owner, 'SilvercartProductAttributeValue', $this->owner->SilvercartProductAttributeValues());
                $attributeField->getConfig()->addComponent($subObjectComponent);
            }
        }
        return;
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
     * On after write.
     * Adds variant modifications to related attribute values.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.09.2016
     */
    public function onAfterWrite() {
        if (array_key_exists('subItem', $_POST) &&
            is_array($_POST['subItem']) &&
            array_key_exists('variantModification', $_POST['subItem'])) {
            
            $modifications = $_POST['subItem']['variantModification'];
            foreach ($modifications as $attributeValueID => $modification) {
                $attributeValue = $this->owner->SilvercartProductAttributeValues()->byID($attributeValueID);
                $extraFields    = array(
                    'ModifyTitleAction'         => '',
                    'ModifyTitleValue'          => '',
                    'ModifyPriceAction'         => '',
                    'ModifyPriceValue'          => '',
                    'ModifyProductNumberAction' => '',
                    'ModifyProductNumberValue'  => '',
                );
                if (array_key_exists('Title', $modification) &&
                    is_array($modification['Title']) &&
                    array_key_exists('action', $modification['Title']) &&
                    array_key_exists('value', $modification['Title'])) {
                    $extraFields['ModifyTitleAction'] = $modification['Title']['action'];
                    $extraFields['ModifyTitleValue'] = $modification['Title']['value'];
                }
                if (array_key_exists('Price', $modification) &&
                    is_array($modification['Price']) &&
                    array_key_exists('action', $modification['Price']) &&
                    array_key_exists('value', $modification['Price'])) {
                    $extraFields['ModifyPriceAction'] = $modification['Price']['action'];
                    $extraFields['ModifyPriceValue'] = $modification['Price']['value'];
                }
                if (array_key_exists('ProductNumber', $modification) &&
                    is_array($modification['ProductNumber']) &&
                    array_key_exists('action', $modification['ProductNumber']) &&
                    array_key_exists('value', $modification['ProductNumber'])) {
                    $extraFields['ModifyProductNumberAction'] = $modification['ProductNumber']['action'];
                    $extraFields['ModifyProductNumberValue'] = $modification['ProductNumber']['value'];
                }
                $this->owner->SilvercartProductAttributeValues()->add($attributeValue, $extraFields);
            }
        }
    }

    /**
     * Returns the products attributes with related values
     * 
     * @return ArrayList
     */
    public function getAttributesWithValues() {
        if (is_null($this->attributesWithValues)) {
            $this->attributesWithValues = new ArrayList();
            foreach ($this->owner->SilvercartProductAttributes() as $attribute) {
                $attributedValues = $this->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
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
     * @return DataList
     */
    public function getAttributedValuesFor($attribute) {
        $assignedValueIDs   = array();
        if (is_null($this->relatedAttributeValueMap)) {
            $this->relatedAttributeValueMap = $this->owner->SilvercartProductAttributeValues()->map('ID', 'ID')->toArray();
        }
        $attributeMap = $attribute->SilvercartProductAttributeValues()->map('ID', 'ID')->toArray();

        foreach ($this->relatedAttributeValueMap as $attributeValueID) {
            if (array_key_exists($attributeValueID, $attributeMap)) {
                $assignedValueIDs[] = $attributeValueID;
            }
        }
        if (count($assignedValueIDs) > 0) {
            $attributedValues = SilvercartProductAttributeValue::get()
                    ->where(
                            sprintf(
                                    "SilvercartProductAttributeValue.ID IN (%s)",
                                    implode(',', $assignedValueIDs)
                            )
                    );
        } else {
            $attributedValues = new ArrayList();
        }
        return $attributedValues;
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
     * Returns whether this product has single product variants or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function hasSingleProductVariants() {
        $hasVariants       = false;
        $variantAttributes = $this->getSingleProductVariantAttributes();
        if ($variantAttributes->exists()) {
            $hasVariants = true;
        }
        return $hasVariants;
    }
    
    /**
     * Returns the single product variant attributes.
     * 
     * @return DataList
     */
    public function getSingleProductVariantAttributes() {
        return $this->owner->SilvercartProductAttributes()->filter('CanBeUsedForSingleVariants', true);
    }
    
    /**
     * Returns the variants for the given Attribute ID
     * 
     * @param int $attributeID Attribute ID to get variants for
     * 
     * @return ArrayList
     */
    public function getVariantsFor($attributeID) {
        $matchedVariants            = new ArrayList();
        $matchingAttributeValues    = new ArrayList();
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
                $matchedVariants->push($variant);
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
     * Returns the context product to get variant data for.
     * 
     * @return SilvercartProduct
     */
    public function getVariantAttributeContext() {
        $context = $this->owner;
        if ($context->IsNotBuyable &&
            $context->hasVariants()) {
            $context = $context->getVariants()->First();
        }
        return $context;
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return ArrayList
     */
    public function getVariantAttributes() {
        $context            = $this->getVariantAttributeContext();
        $attributes         = $context->SilvercartProductAttributes();
        $variantAttributes  = $attributes->filter('CanBeUsedForVariants', true);
        if (!$variantAttributes instanceof SS_List ||
            !$variantAttributes->exists()) {
            $variantAttributes = new ArrayList();
        }
        return $variantAttributes;
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return ArrayList
     */
    public function getVariantAttributeValues() {
        $context                = $this->getVariantAttributeContext();
        $variantAttributeValues = new ArrayList();
        $variantAttributes      = $this->getVariantAttributes();
        foreach ($variantAttributes as $variantAttribute) {
            $variantAttributeValue = $context->SilvercartProductAttributeValues()->find('SilvercartProductAttributeID', $variantAttribute->ID);
            if ($variantAttributeValue) {
                $variantAttributeValues->push($variantAttributeValue);
            }
        }
        return $variantAttributeValues;
    }

    /**
     * Returns the variants of this product
     * 
     * @return DataList
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
            $activeVariants = $variants->filter('isActive',1);
            if (!is_null($activeVariants) &&
                $activeVariants->exists()) {
                $this->variants = $activeVariants;
            }
        }
        return $this->variants;
    }
    
    /**
     * Sets the variants for this product
     * 
     * @param DataList $variants Variants to use
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
     * Returns the variants for the given Attribute ID
     * 
     * @param SilvercartProduct          $product   Product to check variation for
     * @param SilvercartProductAttribute $attribute Attribute to check variation for
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.02.2014
     */
    public function isVariantOf($product, $attribute) {
        $isVariantOf = false;
        if ($this->owner->isVariant()) {
            
            $a = $this->owner->getAttributedValuesFor($attribute);
            $b = $product->getAttributedValuesFor($attribute);
            if (array_shift(array_keys($a->map())) != array_shift(array_keys($b->map()))) {
                $isVariantOf       = true;
                $variantAttributes = $this->getVariantAttributes();
                $variantAttributes->remove($variantAttributes->find('ID', $attribute->ID));

                if ($variantAttributes->Count() > 0) {
                    $matchesWithAll = true;
                    foreach ($variantAttributes as $variantAttribute) {
                        $a = $this->owner->getAttributedValuesFor($variantAttribute);
                        $b = $product->getAttributedValuesFor($variantAttribute);

                        if (array_shift(array_keys($a->map())) != array_shift(array_keys($b->map()))) {
                            $matchesWithAll = false;
                        }
                    }
                    $isVariantOf = $matchesWithAll;
                }
            }
        }
        return $isVariantOf;
    }

    /**
     * Returns whether this product can be used as variant or not
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function CanBeUsedAsVariant() {
        $canBeUsedAsVariant         = false;
        $owner                      = $this->owner;
        $productAttributes          = $owner->SilvercartProductAttributes();
        $variantProductAttributes   = $productAttributes->filter('CanBeUsedForVariants',1);
        if ((!is_null($variantProductAttributes) &&
             $variantProductAttributes->exists()) ||
            $this->owner->IsNotBuyable) {
            $canBeUsedAsVariant = true;
        }
        return $canBeUsedAsVariant;
    }


    /**
     * Returns this products slave products if exists
     * 
     * @return DataList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function getSlaveProducts() {
        $slaves = SilvercartProduct::get()->filter('SilvercartMasterProductID', $this->owner->ID);
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
        $product    = $this->getVariantAttributeContext();
        //$product    = $this->owner;
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
                    if ($product->isVariantOf($variant, $attribute)) {
                        $attributedValues->merge($variant->getAttributedValuesFor($attribute));
                        $variantMap = $variant->getAttributedValuesFor($attribute)->map('ID','ID');
                        foreach ($variantMap as $ID) {
                            if ($ID != $selectedValue) {
                                $fieldModifierNotes[$ID] = $addition;
                            }
                        }
                    }
                }
                $attributedValues->sort('Title');

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

                if (count($values) > 0) {
                    $fieldType = 'SilvercartProductAttributeDropdownField';

                    if (!empty($attribute->useCustomFormField)) {
                        $fieldType = $attribute->useCustomFormField;
                    }

                    $checkRequirements = array();
                    $contextProduct    = $product;
                    
                    if ($product->ID != $this->owner->ID &&
                        $this->owner->IsNotBuyable) {
                        $values         = array('' => _t('SilvercartOrderSearchForm.PLEASECHOOSE')) + $values;
                        $selectedValue  = _t('SilvercartOrderSearchForm.PLEASECHOOSE');
                        $contextProduct = $this->owner;
                    }
                    
                    $fieldGroup['SilvercartProductAttribute' . $attribute->ID] = array(
                        'type'              => $fieldType,
                        'title'             => $attribute->Title,
                        'value'             => $values,
                        'selectedValue'     => $selectedValue,
                        'silvercartProduct' => $contextProduct,
                        'checkRequirements' => $checkRequirements
                    );
                }
            }
        }
        return $fieldGroup;
    }
    
    /**
     * Returns the form fields for the choice of a products single variant to 
     * use with CustomHtmlForm
     * 
     * @return array
     */
    public function getSingleProductVariantFormFields() {
        $product    = $this->owner;
        $fieldGroup = array();
        if ($product->hasSingleProductVariants()) {
            $attributes         = $product->getSingleProductVariantAttributes();
            $fieldModifierNotes = array();

            foreach ($attributes as $attribute) {
                $values         = array();
                $priceAmounts   = array();
                $selectedValue  = 0;

                $attributedValues = $product->SilvercartProductAttributeValues()->filter('SilvercartProductAttributeID', $attribute->ID);
                if ($attributedValues->exists()) {
                    $selectedValue    = $attributedValues->filter('IsDefault', true);
                    if (!($selectedValue instanceof SilvercartProductAttributeValue)) {
                        $selectedValue = $attributedValues->first();
                    }
                    $selectedValue = $selectedValue->ID;
                }
                $attributedValues->sort('Title');

                foreach ($attributedValues as $attributedValue) {
                    if (!$attributedValue->IsActive) {
                        continue;
                    }
                    $attributeName = $attributedValue->Title;
                    $addition      = '';
                    $priceAmount   = new Money();
                    $priceAmount->setAmount($product->getPrice()->getAmount());
                    if ($attributedValue->ModifyPriceValue > 0) {
                        if ($attributedValue->ModifyPriceAction == 'add') {
                            $additionMoney = new SilvercartMoney();
                            $additionMoney->setAmount($attributedValue->ModifyPriceValue);
                            $additionMoney->setCurrency($product->getPrice()->getCurrency());
                            $addition = '+' . $additionMoney->Nice();
                            $priceAmount->setAmount($priceAmount->getAmount() + $additionMoney->getAmount());
                        } elseif ($attributedValue->ModifyPriceAction == 'subtract') {
                            $additionMoney = new SilvercartMoney();
                            $additionMoney->setAmount($attributedValue->ModifyPriceValue);
                            $additionMoney->setCurrency($product->getPrice()->getCurrency());
                            $addition = '-' . $additionMoney->Nice();
                            $priceAmount->setAmount($priceAmount->getAmount() - $additionMoney->getAmount());
                        } elseif ($attributedValue->ModifyPriceAction == 'setTo') {
                            $additionMoney = new SilvercartMoney();
                            $additionMoney->setAmount($attributedValue->ModifyPriceValue);
                            $additionMoney->setCurrency($product->getPrice()->getCurrency());
                            $addition = $additionMoney->Nice();
                            $priceAmount->setAmount($additionMoney->getAmount());
                        }
                    }

                    if (!empty($addition)) {
                        $attributeName .= ' (' . $addition . ')';
                    }

                    $values[$attributedValue->ID] = $attributeName;
                    $priceAmounts[$attributedValue->ID] = $priceAmount->Nice();
                }

                if (count($values) > 0) {
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
                        'checkRequirements' => $checkRequirements,
                        'data'              => array(
                            'prices'     => json_encode($priceAmounts),
                            'type'       => 'single-variant',
                            'product-id' => $product->ID,
                        ),
                    );
                }
            }
        }
        return $fieldGroup;
    }
    
    /**
     * Adds a product to the SilvercartShoppingCart and attaches the given attributes to the 
     * position.
     *
     * @param int   $cartID              ID of the users shopping cart
     * @param int   $quantity            Amount of products to be added
     * @param array $attributes          The attributes that shall be attached to the created position
     * @param array $userInputAttributes Optional: the user generated attributes that shall be attached to the created position
     *
     * @return SilvercartShoppingCartPosition
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function SPAPaddToCartWithAttributes($cartID, $quantity = 1, $attributes = array(), $userInputAttributes =  false) {
        if ($userInputAttributes === false) {
            $userInputAttributes = array();
        }
        
        if (empty($userInputAttributes) &&
            (!is_array($attributes) ||
              count($attributes) == 0)) {

            return $this->owner->addToCart($cartID, $quantity, true);
        }
        $serializedAttributes = serialize($attributes);
        $shoppingCartPosition = SilvercartShoppingCartPosition::get()
                ->filter(array(
                    'SilvercartShoppingCartID'    => $cartID,
                    'SilvercartProductID'         => $this->owner->ID,
                    'SilvercartProductAttributes' => $serializedAttributes,
                ))
                ->first();
        if (!($shoppingCartPosition instanceof SilvercartShoppingCartPosition) ||
            !$shoppingCartPosition->exists()) {
            
            $shoppingCartPosition = new SilvercartShoppingCartPosition();
            $shoppingCartPosition->SilvercartShoppingCartID    = $cartID;
            $shoppingCartPosition->SilvercartProductID         = $this->owner->ID;
            $shoppingCartPosition->SilvercartProductAttributes = $serializedAttributes;
            $shoppingCartPosition->write();
        }
        
        if ($shoppingCartPosition->isQuantityIncrementableBy($quantity)) {
            $shoppingCartPosition->Quantity += $quantity;
        } else {
            if ($this->owner->StockQuantity > 0) {
                $shoppingCartPosition->Quantity += $this->owner->StockQuantity - $shoppingCartPosition->Quantity;
                $shoppingCartPosition->write(); //we have to write because we need the ID
                SilvercartShoppingCartPositionNotice::setNotice($shoppingCartPosition->ID, "remaining");  
            } else {
                return false;
            }
        }
        $shoppingCartPosition->write();
        
        return $shoppingCartPosition;
    }
    
}
