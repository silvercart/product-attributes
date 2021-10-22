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
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\GroupedList;
use SilverStripe\ORM\SS_List;
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
 * 
 * @property Product $owner Owner
 */
class ProductExtension extends DataExtension
{
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
     * @var ArrayList[] 
     */
    protected $variants = [];
    /**
     * Field list vor variation data
     *
     * @var array
     */
    protected $variantFieldList = [];
    /**
     * A set of the products attributes with the related values
     *
     * @var ArrayList[]
     */
    protected $attributesWithValues = [];
    /**
     * A set of the products attributes with the related values
     *
     * @var ArrayList[]
     */
    protected $dataSheetAttributesWithValues = [];
    
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
     * Stores the hasSingleProductVariants options.
     * 
     * @var bool[]
     */
    protected $hasSingleProductVariants = [];
    /**
     * Stores the hasVariants options.
     * 
     * @var bool[]
     */
    protected $hasVariants = [];
    /**
     * Variant form fields.
     * 
     * @var array
     */
    protected $variantFormFields = [];
    /**
     * Single variant form fields.
     * 
     * @var array
     */
    protected $singleProductVariantFormFields = [];
    
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
    public function updateCMSFields(FieldList $fields) : void
    {
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
        
        if ($this->owner->exists()
         && $this->CanBeUsedAsVariant()
        ) {
            if ($this->hasVariants()) {
                $this->addSlaveProductsField($fields);
            }
            if (!$this->isMasterProduct()) {
                $masterProductField = TextField::create('MasterProductNumber', $this->owner->fieldLabel('MasterProduct'), $this->owner->MasterProduct()->ProductNumberShop);
                $fields->addFieldToTab('Root.ProductAttributes', $masterProductField);
            }
        }
    }
    
    /**
     * Adds the slave product fields.
     * 
     * @param FieldList $fields Fields
     * 
     * @return Product
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addSlaveProductsField(FieldList $fields) : Product
    {
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
        $slaveProductsField = GridField::create('SlaveProducts', $this->owner->fieldLabel('SlaveProducts'), $slaveProducts);
        $fields->addFieldToTab('Root.ProductAttributes', $slaveProductsField);
        return $this->owner;
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
    public function updateFieldLabels(&$labels) : void
    {
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
    public function updateShortDescription(string &$shortDescription = null) : void
    {
        if (empty($shortDescription)
         && $this->isSlaveProduct()
        ) {
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
    public function updateLongDescription(string &$longDescription = null) : void
    {
        if (empty($longDescription)
         && $this->isSlaveProduct()
        ) {
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
    public function onBeforeWrite() : void
    {
        if (array_key_exists('MasterProductNumber', $_POST)
         && $this->owner->canEdit()
        ) {
            $masterProductNumber = $_POST['MasterProductNumber'];
            $masterProduct       = Product::get()->filter('ProductNumberShop', $masterProductNumber)->first();
            if ($masterProduct instanceof Product
             && $masterProduct->exists()
            ) {
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
    public function onAfterWrite() : void
    {
        if (array_key_exists('subItem', $_POST)
         && is_array($_POST['subItem'])
         && array_key_exists('variantModification', $_POST['subItem'])
        ) {
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
                if (array_key_exists('Title', $modification)
                 && is_array($modification['Title'])
                 && array_key_exists('action', $modification['Title'])
                 && array_key_exists('value', $modification['Title'])
                ) {
                    $extraFields['ModifyTitleAction'] = $modification['Title']['action'];
                    $extraFields['ModifyTitleValue'] = $modification['Title']['value'];
                }
                if (array_key_exists('Price', $modification)
                 && is_array($modification['Price'])
                 && array_key_exists('action', $modification['Price'])
                 && array_key_exists('value', $modification['Price'])
                ) {
                    $extraFields['ModifyPriceAction'] = $modification['Price']['action'];
                    $extraFields['ModifyPriceValue'] = $modification['Price']['value'];
                }
                if (array_key_exists('ProductNumber', $modification)
                 && is_array($modification['ProductNumber'])
                 && array_key_exists('action', $modification['ProductNumber'])
                 && array_key_exists('value', $modification['ProductNumber'])
                ) {
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
    public function getAttributesWithValues() : ArrayList
    {
        $this->owner->extend('overwriteAttributesWithValues', $this->attributesWithValues);
        if (!array_key_exists($this->owner->ID, $this->attributesWithValues)) {
            $attributesWithValues = ArrayList::create();
            foreach ($this->owner->ProductAttributes() as $attribute) {
                $attributedValues = $this->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
                    $attributesWithValues->push(ArrayData::create([
                        'Attribute' => $attribute,
                        'Values'    => $attributedValues,
                    ]));
                }
            }
            $this->owner->extend('updateAttributesWithValues', $attributesWithValues);
            $this->attributesWithValues[$this->owner->ID] = $attributesWithValues;
        }
        return $this->attributesWithValues[$this->owner->ID];
    }

    /**
     * Returns the products attributes with related values
     * 
     * @return ArrayList
     */
    public function getDataSheetAttributesWithValues() : ArrayList
    {
        $this->owner->extend('overwriteDataSheetAttributesWithValues', $this->dataSheetAttributesWithValues);
        if (!array_key_exists($this->owner->ID, $this->dataSheetAttributesWithValues)) {
            $attributesWithValues = ArrayList::create();
            foreach ($this->owner->ProductAttributes()->filter('CanBeUsedForDataSheet', true) as $attribute) {
                $attributedValues = $this->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
                    $attributesWithValues->push(ArrayData::create([
                        'Attribute' => $attribute,
                        'Values'    => $attributedValues,
                    ]));
                }
            }
            $this->owner->extend('updateDataSheetAttributesWithValues', $attributesWithValues);
            $this->dataSheetAttributesWithValues[$this->owner->ID] = $attributesWithValues;
        }
        return $this->dataSheetAttributesWithValues[$this->owner->ID];
    }

    /**
     * Returns the products attributed values for the given attribute
     * 
     * @param ProductAttribute $attribute Attribute to get values for
     * 
     * @return DataList
     */
    public function getAttributedValuesFor(ProductAttribute $attribute) : ArrayList
    {
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
            $pavTableName     = ProductAttributeValue::config()->table_name;
            $assignedValueStr = implode(',', $assignedValueIDs);
            $attributedValues = ArrayList::create(ProductAttributeValue::get()
                    ->where("{$pavTableName}.ID IN ({$assignedValueStr})")
                    ->toArray()
            );
        } else {
            $attributedValues = ArrayList::create();
        }
        return $attributedValues;
    }

    /**
     * Returns whether this product has variants or not
     * 
     * @return bool
     */
    public function hasVariants() : bool
    {
        if (!array_key_exists($this->owner->ID, $this->hasVariants)) {
            $hasVariants = false;
            $variants    = $this->getVariants();
            if (!is_null($variants)
             && $variants->count() > 0
            ) {
                $hasVariants = true;
            }
            $this->hasVariants[$this->owner->ID] = $hasVariants;
        }
        return $this->hasVariants[$this->owner->ID];
    }

    /**
     * Returns whether this product has single product variants or not
     * 
     * @return bool
     */
    public function hasSingleProductVariants() : bool
    {
        if (!array_key_exists($this->owner->ID, $this->hasSingleProductVariants)) {
            $hasVariants       = false;
            $variantAttributes = $this->getSingleProductVariantAttributes();
            if ($variantAttributes->exists()) {
                $hasVariants = true;
            }
            $this->hasSingleProductVariants[$this->owner->ID] = $hasVariants;
        }
        return $this->hasSingleProductVariants[$this->owner->ID];
    }
    
    /**
     * Returns the single product variant attributes.
     * 
     * @return DataList
     */
    public function getSingleProductVariantAttributes() : DataList
    {
        return $this->owner->ProductAttributes()->filter('CanBeUsedForSingleVariants', true);
    }
    
    /**
     * Returns the variants for the given Attribute ID
     * 
     * @param int $attributeID Attribute ID to get variants for
     * 
     * @return ArrayList
     */
    public function getVariantsFor(int $attributeID) : ArrayList
    {
        $matchedVariants         = ArrayList::create();
        $matchingAttributeValues = ArrayList::create();
        $variants                = $this->getVariants();
        $variantAttributes       = $this->getVariantAttributes();
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
    public function getVariantBy(array $attributeValueIDs) : ?Product
    {
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
    public function getVariantAttributeContext() : Product
    {
        $context = $this->owner;
        if ($context->IsNotBuyable
         && $context->hasVariants()
        ) {
            $context = $context->getVariants()->First();
        }
        return $context;
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return ArrayList
     */
    public function getVariantAttributes() : ArrayList
    {
        $context    = $this->getVariantAttributeContext();
        $attributes = $context->ProductAttributes();
        return ArrayList::create($attributes->filter('CanBeUsedForVariants', true)->toArray());
    }
    
    /**
     * Returns the product attributes as a comma seperated list (last item is 
     * seperated with "&").
     * 
     * @return DBHTMLText
     */
    public function getVariantAttributesNice() : DBHTMLText
    {
        return DBHTMLText::create()->setValue($this->getVariantAttributes()->implode('Title', ', ', ' & '));
    }
    
    /**
     * Returns the product attributes which can be used for variants
     * 
     * @return ArrayList
     */
    public function getVariantAttributeValues() : ArrayList
    {
        $context                = $this->getVariantAttributeContext();
        $variantAttributeValues = ArrayList::create();
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
     * @return ArrayList
     */
    public function getVariants() : ArrayList
    {
        if (!array_key_exists($this->owner->ID, $this->variants)
         && $this->isVariant()
        ) {
            if ($this->isSlaveProduct()) {
                $master = $this->owner->MasterProduct();
            } else {
                $master = $this->owner;
            }
            $variants = $master->getSlaveProducts();
            if ($this->isSlaveProduct()) {
                $arrayList = ArrayList::create($variants->toArray());
                $arrayList->push($master);
            } else {
                $arrayList = ArrayList::create($variants->toArray());
            }
            $activeVariants = $arrayList->filter('isActive',1);
            if (!is_null($activeVariants)
             && $activeVariants->exists()
            ) {
                $this->variants[$this->owner->ID] = $activeVariants;
            }
        }
        if (!array_key_exists($this->owner->ID, $this->variants)) {
            $this->variants[$this->owner->ID] = ArrayList::create();
        }
        return $this->variants[$this->owner->ID]->exclude('ID', $this->owner->ID);
    }
    
    /**
     * Sets the variants for this product
     * 
     * @param DataList $variants Variants to use
     * 
     * @return Product
     */
    public function setVariants($variants) : Product
    {
        $this->variants = $variants;
        return $this->owner;
    }


    /**
     * Returns whether this product is a variant of another product
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isVariant() : bool
    {
        $isVariant = false;
        if ($this->CanBeUsedAsVariant()
         && ($this->isMasterProduct()
          || $this->isSlaveProduct())
        ) {
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
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.06.2018
     */
    public function isVariantOf(Product $product, ProductAttribute $attribute) : bool
    {
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
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function CanBeUsedAsVariant() : bool
    {
        $canBeUsedAsVariant         = false;
        $owner                      = $this->owner;
        $productAttributes          = $owner->ProductAttributes();
        $variantProductAttributes   = $productAttributes->filter('CanBeUsedForVariants',1);
        if ((!is_null($variantProductAttributes)
          && $variantProductAttributes->exists())
         || $this->owner->IsNotBuyable
        ) {
            $canBeUsedAsVariant = true;
        }
        return $canBeUsedAsVariant;
    }


    /**
     * Returns this products slave products if exists
     * 
     * @return DataList
     */
    public function getSlaveProducts() : DataList
    {
        return Product::get()->filter('MasterProductID', $this->owner->ID);
    }
    
    /**
     * Returns whether this is a master product or not
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isMasterProduct() : bool
    {
        $isMasterProduct = false;
        $slaves          = $this->getSlaveProducts();
        if ($slaves
         && $slaves->Count() > 0
        ) {
            $isMasterProduct = true;
        }
        return $isMasterProduct;
    }
    
    /**
     * Returns whether this is a slave product or not
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function isSlaveProduct() : bool
    {
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
    public function getVariantFormFields() : array
    {
        if (array_key_exists($this->owner->ID, $this->variantFormFields)) {
            return $this->variantFormFields[$this->owner->ID];
        }
        $product = $this->getVariantAttributeContext();
        $fields  = [];
        if ($product->hasVariants()) {
            $attributes = $product->getVariantAttributes();
            foreach ($attributes as $attribute) {
                $selectedValue    = 0;
                $attributedValues = $product->getAttributedValuesFor($attribute);
                if ($attributedValues->count() > 0) {
                    $selectedValue = $attributedValues->first()->ID;
                }
                $fieldModifierNotes = $this->getVariantFormFieldModifierNotes($product, $attribute, $attributedValues, $selectedValue);
                $values             = $this->getVariantFormFieldAttributeNames($attributedValues, $fieldModifierNotes);

                if (count($values) > 0) {
                    $contextProduct = $product;
                    if ($product->ID != $this->owner->ID
                     && $this->owner->IsNotBuyable
                    ) {
                        $selectedValue  = '';
                        $contextProduct = $this->owner;
                    }
                    if (!empty($attribute->useCustomFormField)
                     && $this->owner->hasMethod('get' . ucfirst($attribute->useCustomFormField))
                    ) {
                        $field = $this->owner->{'get' . ucfirst($attribute->useCustomFormField)}(
                                "ProductAttribute{$attribute->ID}",
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    } else {
                        $field = ProductAttributeDropdownField::create(
                                "ProductAttribute{$attribute->ID}",
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    }
                    $fields[] = $field;
                }
            }
        }
        $this->variantFormFields[$product->ID] = $fields;
        return $this->variantFormFields[$product->ID];
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
    protected function getVariantFormFieldModifierNotes(Product $product, ProductAttribute $attribute, ArrayList &$attributedValues, int $selectedValue) : array
    {
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
     * @param SS_List $attributedValues   Attributed values
     * @param array   $fieldModifierNotes Field modifier notes
     * 
     * @return string[]
     */
    protected function getVariantFormFieldAttributeNames(SS_List $attributedValues, array $fieldModifierNotes) : array
    {
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
    public function getSingleProductVariantFormFields() : array
    {
        if (array_key_exists($this->owner->ID, $this->singleProductVariantFormFields)) {
            return $this->singleProductVariantFormFields[$this->owner->ID];
        }
        $product = $this->owner;
        $fields  = [];
        if ($product->hasSingleProductVariants()) {
            $attributes = $product->getSingleProductVariantAttributes()->filter('IsUserInputField', false);
            foreach ($attributes as $attribute) {
                $values           = [];
                $prices           = [];
                $selectedValue    = 0;
                $attributedValues = $product->ProductAttributeValues()->filter('ProductAttributeID', $attribute->ID);
                if ($attributedValues->exists()) {
                    $selectedAttributeValue = $attributedValues->filter('IsDefault', true)->first();
                    if ($selectedAttributeValue instanceof ProductAttributeValue) {
                        $selectedValue = $selectedAttributeValue->ID;
                    } else {
                        $selectedValue = $attributedValues->first()->ID;
                    }
                    $attributedValues->sort('Title');
                    foreach ($attributedValues as $attributedValue) {
                        if (!$attributedValue->IsActive) {
                            continue;
                        }
                        $attributeName  = $attributedValue->Title;
                        $attributeName .= $this->getVariantPriceStringIfDifferent($attributedValue, $prices, true);
                        $values[$attributedValue->ID] = $attributeName;
                    }
                }
                if (count($values) > 0) {
                    if (!empty($attribute->useCustomFormField)
                     && $this->owner->hasMethod('get' . ucfirst($attribute->useCustomFormField))
                    ) {
                        $field = $this->owner->{'get' . ucfirst($attribute->useCustomFormField)}(
                                "ProductAttribute{$attribute->ID}",
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                    } else {
                        $field = ProductAttributeDropdownField::create(
                                "ProductAttribute{$attribute->ID}",
                                $attribute->Title,
                                $values,
                                $selectedValue
                        );
                        $field->setRequiredForced(true);
                    }
                    $field->setProductID($product->ID)
                            ->setProductPrices(json_encode($prices))
                            ->setProductVariantType(ProductAttributeDropdownField::VARIANT_TYPE_SINGLE);
                    $fields[] = $field;
                }
            }
        }
        $this->singleProductVariantFormFields[$product->ID] = array_merge($fields, $this->getVariantUserInputFields());
        return $this->singleProductVariantFormFields[$product->ID];
    }
    
    /**
     * Returns the form fields for the choice of a products user input variant
     * fields.
     * 
     * @return array
     */
    protected function getVariantUserInputFields() : array
    {
        $fields = [];
        if (!$this->owner->hasSingleProductVariants()) {
            return $fields;
        }
        $userInputAttributes = $this->owner->getSingleProductVariantAttributes()->filter('IsUserInputField', true);
        if ($userInputAttributes->exists()) {
            foreach ($userInputAttributes as $userInputAttribute) {
                $options         = [];
                $prices          = [];
                $userInputValues = $this->owner->ProductAttributeValues()->filter('ProductAttributeID', $userInputAttribute->ID);
                if ($userInputValues->exists()) {
                    if ($userInputValues->count() > 1
                     && !$userInputAttribute->UserInputFieldMustBeFilledIn
                    ) {
                        $options[''] = $this->owner->fieldLabel('NoUserInput');
                    }
                    foreach ($userInputValues as $value) {
                        $options[$value->ID] = $value->Title . $this->getVariantPriceStringIfDifferent($value, $prices, true);
                    }
                } else {
                    $options[0] = $userInputAttribute->Title;
                }
                
                $field = ChooseEngravingField::create(
                        "ProductAttribute{$userInputAttribute->ID}",
                        $userInputAttribute->Title,
                        $options,
                        ''
                );
                $field->setProductID($this->owner->ID)
                        ->setProductPrices(json_encode($prices))
                        ->setProductVariantType(ProductAttributeDropdownField::VARIANT_TYPE_SINGLE);
                if ($userInputAttribute->UserInputFieldMustBeFilledIn) {
                    $field->setRequiredForced(true);
                }
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
    protected function getVariantPriceStringIfDifferent(ProductAttributeValue $attributeValue, array &$prices, bool $returnAsAddition = false) : string
    {
        $priceString = '';
        $totalPrice  = DBMoney::create();
        $addition    = DBMoney::create();
        $priceAmount = MoneyField::create('tmp')->prepareAmount($attributeValue->FinalModifyPriceValue);
        if ($priceAmount > 0) {
            switch ($attributeValue->FinalModifyPriceAction) {
                case 'add':
                    $priceIsModified = true;
                    $totalPrice->setAmount($this->owner->getPrice()->getAmount() + $priceAmount);
                    $addition->setAmount($priceAmount);
                    break;
                case 'subtract':
                    $priceIsModified = true;
                    $totalPrice->setAmount($this->owner->getPrice()->getAmount() - $priceAmount);
                    $addition->setAmount($priceAmount * -1);
                    break;
                case 'setTo':
                    $priceIsModified = true;
                    $totalPrice->setAmount($priceAmount);
                    $addition->setAmount($priceAmount - $this->owner->getPrice()->getAmount());
                    break;
                default:
                    break;
            }
        }
        if ($returnAsAddition) {
            if ($addition->getAmount() != 0) {
                $sign = '+';
                if ($addition->getAmount() < 0) {
                    $sign = '';
                }
                $priceString = " ({$sign}{$addition->Nice()})";
            }
        } elseif ($totalPrice->getAmount() > 0) {
            $priceString = " ({$totalPrice->Nice()})";
        }
        $prices[$attributeValue->ID] = $totalPrice->Nice();
        return $priceString;
    }
    
    /**
     * Updates the original addToCart() method.
     * 
     * @param int                  $cartID               Cart ID
     * @param int                  $quantity             Quantity to add
     * @param bool                 $increment            Increment instead of adding an absolute quantity?
     * @param bool                 $addToCartAllowed     Add to cart action is allowed?
     * @param ShoppingCartPosition $shoppingCartPosition Cart position
     * 
     * @return void
     */
    public function updateAddToCart(int $cartID, int $quantity, bool $increment, bool &$addToCartAllowed, ShoppingCartPosition $shoppingCartPosition = null) : void
    {
        $product = $this->owner;
        if ($product->hasSingleProductVariants()) {
            $formData = $_POST;
            foreach ($this->getSingleProductVariantAttributes() as $attribute) {
                $formFieldName = "ProductAttribute{$attribute->ID}";
                if ((!$attribute->IsUserInputField
                  || ($attribute->IsUserInputField
                   && $attribute->UserInputFieldMustBeFilledIn))
                 && (!array_key_exists($formFieldName, $formData)
                  || empty($formData[$formFieldName]))
                ) {
                    $addToCartAllowed = false;
                    if (Controller::has_curr()
                     && !Controller::curr()->redirectedTo()
                    ) {
                        Controller::curr()->redirect($this->owner->Link());
                        return;
                    }
                }
            }
        }
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
    public function SPAPaddToCartWithAttributes(int $cartID, int $quantity = 1, array $attributes = []) : ?ShoppingCartPosition
    {
        if (!is_array($attributes)
         || count($attributes) == 0
        ) {
            return $this->owner->addToCart($cartID, $quantity, true);
        }
        $increment            = true;
        $addToCartAllowed     = true;
        $shoppingCartPosition = null;
        $this->owner->extend('updateAddToCart', $cartID, $quantity, $increment, $addToCartAllowed, $shoppingCartPosition);
        if ($this->owner->IsNotBuyable
         || $quantity == 0
         || $cartID == 0
         || !$addToCartAllowed
         || !$this->owner->isBuyableDueToStockManagementSettings()
        ) {
            return $shoppingCartPosition;
        }
        $isNewPosition        = false;
        $serializedAttributes = serialize($attributes);
        $shoppingCartPosition = ShoppingCartPosition::get()
                ->filter([
                    'ShoppingCartID'    => $cartID,
                    'ProductID'         => $this->owner->ID,
                    'ProductAttributes' => $serializedAttributes,
                ])
                ->first();
        if (!($shoppingCartPosition instanceof ShoppingCartPosition)
         || !$shoppingCartPosition->exists()
        ) {
            if ($quantity <= 0) {
                return null;
            }
            $isNewPosition        = true;
            $shoppingCartPosition = ShoppingCartPosition::create()
                    ->castedUpdate([
                        'ShoppingCartID'    => $cartID,
                        'ProductID'         => $this->owner->ID,
                        'ProductAttributes' => $serializedAttributes,
                    ]);
        }
        
        $quantityToAdd = $quantity - $shoppingCartPosition->Quantity;
        if ($shoppingCartPosition->isQuantityIncrementableBy($quantityToAdd, $this->owner)) {
            $shoppingCartPosition->Quantity += $quantityToAdd;
        } elseif ($this->owner->StockQuantity > 0) {
            $shoppingCartPosition->Quantity += $this->owner->StockQuantity - $shoppingCartPosition->Quantity;
            $shoppingCartPosition->write(); //we have to write because we need the ID
            ShoppingCartPositionNotice::setNotice($shoppingCartPosition->ID, "remaining");  
        } else {
            return null;
        }
        $shoppingCartPosition->write();
        $this->owner->extend('onAfterAddToCart', $shoppingCartPosition, $isNewPosition);
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
    public function addPluggedInTab(ArrayList &$pluggedInTabs) : void
    {
        if ($this->owner->ProductAttributes()->filter('CanBeUsedForDataSheet', true)->exists()
         && $this->owner->ProductAttributeValues()->exists()
        ) {
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
            $attributes      = GroupedList::create($valuesWithImage)->groupBy('ProductAttributeID');
            $attributeIDs    = array_keys($attributes);
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
     * @return void 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addPluggedInAfterImageContent(ArrayList &$pluggedInAfterImageContent) : void
    {
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
    public function Headings(SS_List $variants) : ArrayList
    {
        $headings = ArrayList::create();
        $headings->push(ArrayData::create(["Name" => 'ProductNumber', "Title" => $this->owner->fieldLabel('ProductNumberShop')]));
        $headings->push(ArrayData::create(["Name" => 'Title',         "Title" => $this->owner->fieldLabel('Title')]));
        $variantAttributes = ArrayList::create();
        foreach ($variants as $item) {
            if ($item instanceof Product
             && $item->exists()
            ) {
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
        $headings->push(ArrayData::create(["Name" => 'Price', "Title" => $this->owner->fieldLabel('Price')]));
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
    public function Items(ArrayList $variants, Product $original) : ArrayList
    {
        if (!$original->IsNotBuyable
         && !$variants->find('ID', $original->ID)
        ) {
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
    public function Fields(Product $product) : ArrayList
    {
        $fields = ArrayList::create();
        $fields->push(ArrayData::create(["Name" => 'ProductNumber', "Value" => $product->ProductNumberShop, "Link" => $product->Link()]));
        $fields->push(ArrayData::create(["Name" => 'Title',         "Value" => $product->Title,             "Link" => $product->Link()]));
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
        $fields->push(ArrayData::create(["Name" => 'Price', "Value" => $product->getPriceNice(), "Link" => $product->Link()]));
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
    public function VariantFieldList() : array
    {
        return $this->variantFieldList;
    }
}