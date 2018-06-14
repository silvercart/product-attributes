<?php

namespace SilverCart\ProductAttributes\Extensions\Product;

use SilverCart\Dev\Tools;
use SilverCart\Forms\FormFields\MoneyField;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Order\ShoppingCartPosition;
use SilverCart\Model\Order\ShoppingCartPositionNotice;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\FieldType\DBMoney;
use SilverCart\ProductAttributes\Admin\Forms\GridField\GridFieldSubObjectHandler;
use SilverCart\ProductAttributes\Forms\FormFields\ChooseEngravingField;
use SilverCart\ProductAttributes\Forms\FormFields\ProductAttributeDropdownField;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\GroupedList;
use SilverStripe\View\ArrayData;

/**
 * Extension for a product.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductExtension extends DataExtension {
    
    /**
     * Many to many relations
     *
     * @var array
     */
    private static $many_many = [
        'ProductAttributes'      => ProductAttribute::class,
        'ProductAttributeValues' => ProductAttributeValue::class,
    ];
    
    /**
     * Extra fields for many to many relations.
     *
     * @var array
     */
    private static $many_many_extraFields = [
        'ProductAttributeValues' => [
            'IsActive'                  => 'Boolean',
            'IsDefault'                 => 'Boolean',
            'ModifyTitleAction'         => 'Enum(",add,setTo","")',
            'ModifyTitleValue'          => "Varchar(256)",
            'ModifyPriceAction'         => 'Enum(",add,subtract,setTo","")',
            'ModifyPriceValue'          => "Float",
            'ModifyProductNumberAction' => 'Enum(",add,setTo","")',
            'ModifyProductNumberValue'  => "Varchar(50)",
        ],
    ];

    /**
     * Set of variants related with this product
     *
     * @var ArrayList 
     */
    protected $variants = null;
    
    /**
     * Field list vor variation data
     *
     * @var array
     */
    protected $variantFieldList = [];
    
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
    protected $relatedAttributeValueMap = [];
    
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
     * @since 30.05.2018
     */
    public function updateCMSFields(FieldList $fields) {
        if (!$this->updateCMSFieldsIsCalled) {
            $this->updateCMSFieldsIsCalled = true;
            $fields->removeByName('ProductAttributeValues');
            if ($this->owner->exists()) {
                $attributeField = $fields->dataFieldByName('ProductAttributes');
                /* @var $attributeField GridField */
                $subObjectComponent = new GridFieldSubObjectHandler($this->owner, ProductAttributeValue::class, $this->owner->ProductAttributeValues());
                $attributeField->getConfig()->addComponent($subObjectComponent);
            }
        }
        
        if ($this->owner->exists() &&
            $this->CanBeUsedAsVariant()) {
            
            if ($this->hasVariants()) {
                $this->addSlaveProductsField($fields);
            }
            if (!$this->isMasterProduct()) {
                $masterProductField = new TextField('MasterProductNumber', $this->owner->fieldLabel('MasterProduct'), $this->owner->MasterProduct()->ProductNumberShop);
                $fields->addFieldToTab('Root.ProductAttributes', $masterProductField);
                
            }
        }
    }
    
    /**
     * Adds the slave product fields.
     * 
     * @param FieldList $fields Fields
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addSlaveProductsField($fields) {
        if ($this->owner->isMasterProduct()) {
            $master = $this->owner;
        } else {
            $master = $this->owner->MasterProduct();
        }
        $filter = [
            'MasterProductID' => $master->ID,
            'ID'              => $master->ID,
        ];
        $slaveProducts      = Product::get()->filterAny($filter);
        $slaveProductsField = new GridField('SlaveProducts', $this->owner->fieldLabel('SlaveProducts'), $slaveProducts);
        $fields->addFieldToTab('Root.ProductAttributes', $slaveProductsField);
    }
    
    /**
     * Updates the field labels
     *
     * @param array &$labels Labels to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
                $labels,
                Tools::field_labels_for(static::class),
                [
                    'MasterProduct'          => _t(static::class . '.MASTER_PRODUCT', 'This product is a variant of the product with the following product number'),
                    'ProductAttributes'      => _t(static::class . '.PRODUCT_ATTRIBUTES', 'Attribute'),
                    'ProductAttributeValues' => _t(static::class . '.PRODUCT_ATTRIBUTE_VALUES', 'Value'),
                    'SlaveProducts'          => _t(static::class . '.SLAVE_PRODUCTS', 'This product has the following variants:'),
                    'ProductAttribute'       => _t(static::class . '.PRODUCT_ATTRIBUTE', 'Attribute'),
                    'ProductAttributeValue'  => _t(static::class . '.PRODUCT_ATTRIBUTE_VALUE', 'Value'),
                    'NoUserInput'            => _t(static::class . '.NoUserInput', 'None'),
                ]
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
     * @since 30.05.2018
     */
    public function updateShortDescription(&$shortDescription) {
        if (empty($shortDescription) &&
            $this->isSlaveProduct()) {
            $shortDescription = $this->owner->MasterProduct()->ShortDescription;
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
     * @since 30.05.2018
     */
    public function updateLongDescription(&$longDescription) {
        if (empty($longDescription) &&
            $this->isSlaveProduct()) {
            $longDescription = $this->owner->MasterProduct()->LongDescription;
        }
    }
    
    /**
     * On before write.
     * Adds variant modifications to related attribute values.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function onBeforeWrite() {
        if (array_key_exists('MasterProductNumber', $_POST) &&
            $this->owner->canEdit()) {
            
            $masterProductNumber = $_POST['MasterProductNumber'];
            $masterProduct       = Product::get()->filter('ProductNumberShop', $masterProductNumber)->first();
            if ($masterProduct instanceof Product &&
                $masterProduct->exists()) {
                $this->owner->MasterProductID = $masterProduct->ID;
            }
        }
    }
    
    /**
     * On after write.
     * Adds variant modifications to related attribute values.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function onAfterWrite() {
        if (array_key_exists('subItem', $_POST) &&
            is_array($_POST['subItem']) &&
            array_key_exists('variantModification', $_POST['subItem'])) {
            
            $modifications = $_POST['subItem']['variantModification'];
            foreach ($modifications as $attributeValueID => $modification) {
                $attributeValue = $this->owner->ProductAttributeValues()->byID($attributeValueID);
                $extraFields    = [
                    'ModifyTitleAction'         => '',
                    'ModifyTitleValue'          => '',
                    'ModifyPriceAction'         => '',
                    'ModifyPriceValue'          => '',
                    'ModifyProductNumberAction' => '',
                    'ModifyProductNumberValue'  => '',
                ];
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
                $this->owner->ProductAttributeValues()->add($attributeValue, $extraFields);
            }
        }
    }

    /**
     * Returns the products attributes with related values
     * 
     * @return ArrayList
     */
    public function getAttributesWithValues() {
        $this->owner->extend('overwriteAttributesWithValues', $this->attributesWithValues);
        if (is_null($this->attributesWithValues)) {
            $this->attributesWithValues = new ArrayList();
            foreach ($this->owner->ProductAttributes() as $attribute) {
                $attributedValues = $this->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
                    $this->attributesWithValues->push(
                            new ArrayData(
                                    [
                                        'Attribute' => $attribute,
                                        'Values'    => $attributedValues,
                                    ]
                            )
                    );
                }
            }
            $this->owner->extend('updateAttributesWithValues', $this->attributesWithValues);
        }
        return $this->attributesWithValues;
    }

    /**
     * Returns the products attributed values for the given attribute
     * 
     * @param ProductAttribute $attribute Attribute to get values for
     * 
     * @return DataList
     */
    public function getAttributedValuesFor($attribute) {
        $assignedValueIDs = [];
        if (!array_key_exists($this->owner->ID, $this->relatedAttributeValueMap)) {
            $this->relatedAttributeValueMap[$this->owner->ID] = $this->owner->ProductAttributeValues()->map('ID', 'ID')->toArray();
        }
        $attributeMap = $attribute->ProductAttributeValues()->map('ID', 'ID')->toArray();

        foreach ($this->relatedAttributeValueMap[$this->owner->ID] as $attributeValueID) {
            if (array_key_exists($attributeValueID, $attributeMap)) {
                $assignedValueIDs[] = $attributeValueID;
            }
        }
        if (count($assignedValueIDs) > 0) {
            $attributedValues = ArrayList::create(ProductAttributeValue::get()
                    ->where(
                            sprintf(
                                    "%s.ID IN (%s)",
                                    ProductAttributeValue::config()->get('table_name'),
                                    implode(',', $assignedValueIDs)
                            )
                    )->toArray());
        } else {
            $attributedValues = ArrayList::create();
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
            $variants->count() > 0) {
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
        return $this->owner->ProductAttributes()->filter('CanBeUsedForSingleVariants', true);
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
            if ($variant->ProductAttributes()->find('ID', $attributeID)) {
                $attributeValueMatches = [];
                foreach ($matchingAttributeValues as $matchingAttributeValue) {
                    if ($variant->ProductAttributeValues()->find('ID', $matchingAttributeValue->ID)) {
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
     * @return Product
     */
    public function getVariantBy($attributeValueIDs) {
        $matchedVariant = null;
        $variants       = $this->getVariants();
        foreach ($variants as $variant) {
            $matched = [];
            foreach ($attributeValueIDs as $attributeValueID) {
                if ($variant->ProductAttributeValues()->find('ID', $attributeValueID)) {
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
     * @return Product
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
        $attributes         = $context->ProductAttributes();
        $variantAttributes  = new ArrayList($attributes->filter('CanBeUsedForVariants', true)->toArray());
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
            $variantAttributeValue = $context->ProductAttributeValues()->find('ProductAttributeID', $variantAttribute->ID);
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
                $master = $this->owner->MasterProduct();
            } else {
                $master = $this->owner;
            }
            $variants = $master->getSlaveProducts();
            if ($this->isSlaveProduct()) {
                $arrayList = new ArrayList($variants->toArray());
                $arrayList->push($master);
            } else {
                $arrayList = new ArrayList($variants->toArray());
            }
            $activeVariants = $arrayList->filter('isActive',1);
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
     * @param Product          $product   Product to check variation for
     * @param ProductAttribute $attribute Attribute to check variation for
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.06.2018
     */
    public function isVariantOf($product, $attribute) {
        $isVariantOf = false;
        if ($this->owner->isVariant()) {
            
            $a = array_keys($this->owner->getAttributedValuesFor($attribute)->map()->toArray());
            $b = array_keys($product->getAttributedValuesFor($attribute)->map()->toArray());
            if (array_shift($a) != array_shift($b)) {
                $isVariantOf       = true;
                $variantAttributes = $this->getVariantAttributes();
                $variantAttributes->remove($variantAttributes->find('ID', $attribute->ID));

                if ($variantAttributes->Count() > 0) {
                    $matchesWithAll = true;
                    foreach ($variantAttributes as $variantAttribute) {
                        $a = array_keys($this->owner->getAttributedValuesFor($variantAttribute)->map()->toArray());
                        $b = array_keys($product->getAttributedValuesFor($variantAttribute)->map()->toArray());

                        if (array_shift($a) != array_shift($b)) {
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
        $productAttributes          = $owner->ProductAttributes();
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
     */
    public function getSlaveProducts() {
        $slaves = Product::get()->filter('MasterProductID', $this->owner->ID);
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
        if ($owner->MasterProduct()->exists()) {
            $isSlaveProduct = true;
        }
        return $isSlaveProduct;
    }
    
    /**
     * Returns the form fields for the choice of a products variant to use.
     * 
     * @return array
     */
    public function getVariantFormFields() {
        $product = $this->getVariantAttributeContext();
        $fields  = [];
        
        if ($product->hasVariants()) {
            $attributes = $product->getVariantAttributes();
            
            foreach ($attributes as $attribute) {
                $selectedValue = 0;

                $attributedValues = $product->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
                    $selectedValue = $attributedValues->first()->ID;
                }
                
                $fieldModifierNotes = $this->getVariantFormFieldModifierNotes($product, $attribute, $attributedValues, $selectedValue);
                $values             = $this->getVariantFormFieldAttributeNames($attributedValues, $fieldModifierNotes);

                if (count($values) > 0) {
                    
                    $contextProduct = $product;
                    
                    if ($product->ID != $this->owner->ID &&
                        $this->owner->IsNotBuyable) {
                        $selectedValue  = '';
                        $contextProduct = $this->owner;
                    }
                    
                    if (!empty($attribute->useCustomFormField) &&
                        $this->owner->hasMethod('get' . ucfirst($attribute->useCustomFormField))) {
                        $field = $this->owner->{'get' . ucfirst($attribute->useCustomFormField)}(
                                'ProductAttribute' . $attribute->ID,
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    } else {
                        $field = ProductAttributeDropdownField::create(
                                'ProductAttribute' . $attribute->ID,
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    }
                    
                    $fields[] = $field;
                }
            }
        }
        
        return $fields;
    }
    
    /**
     * Returns the field modifier notes for the given context.
     * 
     * @param Product          $product           Product
     * @param ProductAttribute $attribute         Attribute
     * @param ArrayList        &$attributedValues Attribute values
     * @param int              $selectedValue     Selected value
     * 
     * @return string[]
     */
    protected function getVariantFormFieldModifierNotes($product, $attribute, &$attributedValues, $selectedValue) {
        $variants           = $product->getVariantsFor($attribute->ID);
        $fieldModifierNotes = [];
        foreach ($variants as $variant) {
            if ($product->isVariantOf($variant, $attribute)) {
                $attributedValues->merge($variant->getAttributedValuesFor($attribute));
                $variantMap = $variant->getAttributedValuesFor($attribute)->map('ID','ID');
                foreach ($variantMap as $ID) {
                    if ($ID != $selectedValue) {
                        $fieldModifierNotes[$ID] = $variant->getPrice()->Nice();
                    }
                }
            }
        }
        return $fieldModifierNotes;
    }
    
    /**
     * Returns a list of attribute names.
     * 
     * @param type $attributedValues
     * @param type $fieldModifierNotes
     * 
     * @return string[]
     */
    protected function getVariantFormFieldAttributeNames($attributedValues, $fieldModifierNotes) {
        $attributedValues->sort('Title');
        $attributeNames = [];
        foreach ($attributedValues as $attributedValue) {
            $attributeName     = $attributedValue->Title;
            $fieldModifierNote = '';
            if (array_key_exists($attributedValue->ID, $fieldModifierNotes)) {
                $fieldModifierNote = $fieldModifierNotes[$attributedValue->ID];
            }

            if (!empty($fieldModifierNote)) {
                $attributeName .= ' (' . $fieldModifierNote . ')';
            }

            $attributeNames[$attributedValue->ID] = $attributeName;
        }
        return $attributeNames;
    }
    
    /**
     * Returns the form fields for the choice of a products single variant to 
     * use.
     * 
     * @return array
     */
    public function getSingleProductVariantFormFields() {
        $product = $this->owner;
        $fields  = [];
        if ($product->hasSingleProductVariants()) {
            $attributes = $product->getSingleProductVariantAttributes()->filter('IsUserInputField', false);

            foreach ($attributes as $attribute) {
                $values        = [];
                $plainValues   = [];
                $prices        = [];
                $selectedValue = 0;

                $attributedValues = $product->ProductAttributeValues()->filter('ProductAttributeID', $attribute->ID);
                if ($attributedValues->exists()) {
                    $selectedAttributeValue = $attributedValues->filter('IsDefault', true);
                    if (!($selectedAttributeValue instanceof ProductAttributeValue)) {
                        $selectedAttributeValue = $attributedValues->first();
                    }
                    $selectedValue = $selectedAttributeValue->ID;
                }
                $attributedValues->sort('Title');

                $priceIsModified = false;
                foreach ($attributedValues as $attributedValue) {
                    if (!$attributedValue->IsActive) {
                        continue;
                    }
                    $attributeName = $attributedValue->Title;
                    $price         = new DBMoney();
                    $price->setAmount($product->getPrice()->getAmount());
                    $price->setCurrency($product->getPrice()->getCurrency());
                    $addition      = $product->getPrice()->Nice();
                    $priceAmount   = MoneyField::create('tmp')->prepareAmount($attributedValue->ModifyPriceValue);
                    if ($priceAmount > 0) {
                        switch ($attributedValue->ModifyPriceAction) {
                            case 'add':
                                $priceIsModified = true;
                                $price->setAmount($product->getPrice()->getAmount() + $priceAmount);
                                $addition = $price->Nice();
                                break;
                            case 'subtract':
                                $priceIsModified = true;
                                $price->setAmount($product->getPrice()->getAmount() - $priceAmount);
                                $addition = $price->Nice();
                                break;
                            case 'setTo':
                                $priceIsModified = true;
                                $price->setAmount($priceAmount);
                                $addition = $price->Nice();
                                break;
                            default:
                                break;
                        }
                    }

                    $plainValues[$attributedValue->ID] = $attributeName;
                    if (!empty($addition)) {
                        $attributeName .= ' (' . $addition . ')';
                    }
                    $values[$attributedValue->ID] = $attributeName;
                    $prices[$attributedValue->ID] = $price->Nice();
                }
                if (!$priceIsModified) {
                    $values = $plainValues;
                    $prices = [];
                }

                if (count($values) > 0) {
                    if (!empty($attribute->useCustomFormField) &&
                        $this->owner->hasMethod('get' . ucfirst($attribute->useCustomFormField))) {
                        $field = $this->owner->{'get' . ucfirst($attribute->useCustomFormField)}(
                                'ProductAttribute' . $attribute->ID,
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    } else {
                        $field = ProductAttributeDropdownField::create(
                                'ProductAttribute' . $attribute->ID,
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    }
                    $field->setProductID($product->ID)
                            ->setProductPrices(json_encode($prices))
                            ->setProductVariantType(ProductAttributeDropdownField::VARIANT_TYPE_SINGLE);
                    $fields[] = $field;
                }
            }
        }
        return array_merge($fields, $this->getVariantUserInputFields());
    }
    
    /**
     * Returns the form fields for the choice of a products user input variant
     * fields.
     * 
     * @return array
     */
    protected function getVariantUserInputFields() {
        $fields = [];
        if (!$this->owner->hasSingleProductVariants()) {
            return $fields;
        }
        $userInputAttributes = $this->owner->getSingleProductVariantAttributes()->filter('IsUserInputField', true);
        if ($userInputAttributes->exists()) {
            foreach ($userInputAttributes as $userInputAttribute) {
                $options = [];
                $requirements = [];
                if (!$userInputAttribute->UserInputFieldMustBeFilledIn) {
                    $options[''] = $this->owner->fieldLabel('NoUserInput');
                } else {
                    $requirements = [
                        'isFilledIn' => true,
                    ];
                }
                $prices          = [];
                $userInputValues = $this->owner->ProductAttributeValues()->filter('ProductAttributeID', $userInputAttribute->ID);
                if ($userInputValues->exists()) {
                    foreach ($userInputValues as $value) {
                        $options[$value->ID] = $value->Title . $this->getVariantPriceStringIfDifferent($value, $prices, true);
                    }
                } else {
                    $options[0] = $userInputAttribute->Title;
                }
                
                $field = ChooseEngravingField::create(
                        'ProductAttribute' . $userInputAttribute->ID,
                        $userInputAttribute->Title,
                        $options,
                        ''
                );
                $field->setProductID($this->owner->ID)
                        ->setProductPrices(json_encode($prices))
                        ->setProductVariantType(ProductAttributeDropdownField::VARIANT_TYPE_SINGLE);
                $fields[] = $field;
            }
        }
        return $fields;
    }
    
    /**
     * Returns the price difference string to show when choosing a product variant.
     * 
     * @param ProductAttributeValue $attributeValue   Attribute value
     * @param array                 &$prices          List of variant prices
     * @param bool                  $returnAsAddition Return string as addition? (e.g. "+15,00 €"/"+$15,00")
     * 
     * @return string
     */
    protected function getVariantPriceStringIfDifferent($attributeValue, &$prices, $returnAsAddition = false) {
        $priceString = '';
        $totalPrice  = DBMoney::create();
        $addition    = DBMoney::create();
        $priceAmount = MoneyField::create('tmp')->prepareAmount($attributeValue->ModifyPriceValue);
        if ($priceAmount > 0) {
            if ($attributeValue->ModifyPriceAction == 'add') {
                $priceIsModified = true;
                $totalPrice->setAmount($this->owner->getPrice()->getAmount() + $priceAmount);
                $addition->setAmount($priceAmount);
            } elseif ($attributeValue->ModifyPriceAction == 'subtract') {
                $priceIsModified = true;
                $totalPrice->setAmount($this->owner->getPrice()->getAmount() - $priceAmount);
                $addition->setAmount($priceAmount * -1);
            } elseif ($attributeValue->ModifyPriceAction == 'setTo') {
                $priceIsModified = true;
                $totalPrice->setAmount($priceAmount);
                $addition->setAmount($priceAmount - $this->owner->getPrice()->getAmount());
            }
        }
        if ($returnAsAddition) {
            if ($addition->getAmount() != 0) {
                $sign = '+';
                if ($addition->getAmount() < 0) {
                    $sign = '-';
                }
                $priceString = ' (' . $sign . $addition->Nice() . ')';
            }
        } elseif ($totalPrice->getAmount() > 0) {
            $priceString = ' (' . $totalPrice->Nice() . ')';
        }
        $prices[$attributeValue->ID] = $totalPrice->Nice();
        return $priceString;
    }
    
    /**
     * Adds a product to the ShoppingCart and attaches the given attributes to the 
     * position.
     *
     * @param int   $cartID              ID of the users shopping cart
     * @param int   $quantity            Amount of products to be added
     * @param array $attributes          The attributes that shall be attached to the created position
     * @param array $userInputAttributes Optional: the user generated attributes that shall be attached to the created position
     *
     * @return ShoppingCartPosition
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function SPAPaddToCartWithAttributes($cartID, $quantity = 1, $attributes = []) {
        if (!is_array($attributes) ||
            count($attributes) == 0) {

            return $this->owner->addToCart($cartID, $quantity, true);
        }
        $serializedAttributes = serialize($attributes);
        $shoppingCartPosition = ShoppingCartPosition::get()
                ->filter([
                    'ShoppingCartID'    => $cartID,
                    'ProductID'         => $this->owner->ID,
                    'ProductAttributes' => $serializedAttributes,
                ])
                ->first();
        if (!($shoppingCartPosition instanceof ShoppingCartPosition) ||
            !$shoppingCartPosition->exists()) {
            
            $shoppingCartPosition = new ShoppingCartPosition();
            $shoppingCartPosition->ShoppingCartID    = $cartID;
            $shoppingCartPosition->ProductID         = $this->owner->ID;
            $shoppingCartPosition->ProductAttributes = $serializedAttributes;
            $shoppingCartPosition->write();
        }
        
        if ($shoppingCartPosition->isQuantityIncrementableBy($quantity)) {
            $shoppingCartPosition->Quantity += $quantity;
        } else {
            if ($this->owner->StockQuantity > 0) {
                $shoppingCartPosition->Quantity += $this->owner->StockQuantity - $shoppingCartPosition->Quantity;
                $shoppingCartPosition->write(); //we have to write because we need the ID
                ShoppingCartPositionNotice::setNotice($shoppingCartPosition->ID, "remaining");  
            } else {
                return false;
            }
        }
        $shoppingCartPosition->write();
        
        return $shoppingCartPosition;
    }
    
    /**
     * Adds a tab for product attribute information information
     *
     * @param ArrayList &$pluggedInTabs List of plugged in tabs
     * 
     * @return void 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addPluggedInTab(ArrayList &$pluggedInTabs) {
        if ($this->owner->ProductAttributes()->filter('CanBeUsedForDataSheet', true)->exists() &&
            $this->owner->ProductAttributeValues()->exists()) {
            $content = $this->owner->renderWith(ProductAttribute::class . '_Tab');
            if (!empty($content)) {
                $pluggedInTabs->push(ArrayData::create([
                    'Name'    => ProductAttribute::singleton()->plural_name(),
                    'Content' => $content,
                ]));
            }
        }
        
        if ($this->owner->ProductAttributeValues()->exclude('ImageID', 0)->exists()) {
            $valuesWithImage = $this->owner->ProductAttributeValues()->filter('IsActive', true)->exclude('ImageID', 0);
            $attributes   = GroupedList::create($valuesWithImage)->groupBy('ProductAttributeID');
            $attributeIDs = array_keys($attributes);
            foreach ($attributeIDs as $attributeID) {
                $attribute = $this->owner->ProductAttributes()->byID($attributeID);
                if (!$attribute->CanBeUsedForSingleVariants) {
                    continue;
                }
                $values    = $this->owner->ProductAttributeValues()->filter([
                    'ProductAttributeID' => $attributeID,
                    'IsActive'           => true,
                ]);
                $content   = $this->owner->customise([
                    'ProductAttributeValuesWithImage' => $values,
                ])->renderWith(ProductAttributeValue::class . '_ImageTab');
                if (!empty($content)) {
                    $pluggedInTabs->push(ArrayData::create([
                        'Name'    => $attribute->PluralTitle,
                        'Content' => $content,
                    ]));
                }
            }
        }
    }
    
    /**
     * Adds some information to display between Images and Content.
     *
     * @param ArrayList &$pluggedInAfterImageContent List of plugged in after image content
     * 
     * @return DataObject 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addPluggedInAfterImageContent(ArrayList &$pluggedInAfterImageContent) {
        if ($this->owner->hasVariants()) {
            $content = $this->owner->customise([
                'Headings' => $this->Headings($this->owner->getVariants()),
                'Items'    => $this->Items($this->owner->getVariants(), $this->owner),
            ])->renderWith(ProductAttribute::class . '_VariantTable');
            if (!empty($content)) {
                $pluggedInAfterImageContent->push(ArrayData::create([
                    'Content' => $content,
                ]));
            }
        }
    }
    
    /**
     * Adds the variation data to the headings and returns them
     * 
     * @param DataList $variants Variants
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Headings($variants) {
        $headings = new ArrayList();
        $headings->push(new ArrayData(["Name" => 'ProductNumber', "Title" => $this->owner->fieldLabel('ProductNumberShop')]));
        $headings->push(new ArrayData(["Name" => 'Title',         "Title" => $this->owner->fieldLabel('Title')]));
        
        $variantAttributes = new ArrayList();
        foreach ($variants as $item) {
            if ($item instanceof Product &&
                $item->exists()) {
                $variantAttributes->merge($item->getVariantAttributes());
            }
        }
        $variantAttributes->removeDuplicates();

        foreach ($variantAttributes as $attribute) {
            $this->variantFieldList[$attribute->ID] = $attribute->Title;
            $headings->push(ArrayData::create([
                "Name"          => 'VariantAttribute' . $attribute->ID,
                "Title"         => $attribute->Title,
                "IsSortable"    => false,
                "SortLink"      => false,
                "SortBy"        => false,
                "SortDirection" => null,
            ]));
        }
        
        $headings->push(new ArrayData(["Name" => 'Price', "Title" => $this->owner->fieldLabel('Price')]));

        return $headings;
    }
    
    /**
     * Returns the items.
     * 
     * @param ArrayList $variants Variants
     * @param Product   $original Original product
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.08.2014
     */
    public function Items($variants, $original) {
        if (!$original->IsNotBuyable &&
            !$variants->find('ID', $original->ID)) {
            $variants->push($original);
        }
        $variants->sort('Title');
        foreach ($variants as $variant) {
            if ($variant->IsNotBuyable) {
                $variants->remove($variant);
            }
            $variant->ItemFields = $this->Fields($variant);
        }
        return $variants;
    }

    /**
     * Adds the variation data to the items fields and returns them
     * 
     * @param Product $product Product
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Fields($product) {
        $fields = new ArrayList();
        $fields->push(new ArrayData(["Name" => 'ProductNumber', "Value" => $product->ProductNumberShop, "Link" => $product->Link()]));
        $fields->push(new ArrayData(["Name" => 'Title',         "Value" => $product->Title,             "Link" => $product->Link()]));
        
        $variantList                 = $this->VariantFieldList();
        $variantAttributeValueGroups = GroupedList::create($product->ProductAttributeValues())->groupBy('ProductAttributeID');
        foreach ($variantList as $variantAttributeID => $variantAttributeTitle) {
            if (array_key_exists($variantAttributeID, $variantAttributeValueGroups)) {
                $fields->push(ArrayData::create([
                    "Name"  => 'VariantAttribute' . $variantAttributeID,
                    "Value" => implode(', ', $variantAttributeValueGroups[$variantAttributeID]->map('ID', 'Title')->toArray()),
                    "Link"  => $product->Link()
                ]));
            }
        }
        
        $fields->push(new ArrayData(["Name" => 'Price', "Value" => $product->getPriceNice(), "Link" => $product->Link()]));
        
        return $fields;
    }
    
    /**
     * Returns the variant field list to use for the items
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function VariantFieldList() {
        return $this->variantFieldList;
    }
    
}