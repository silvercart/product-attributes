<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

use SilverCart\Forms\FormFields\MoneyField;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\FieldType\DBMoney;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

/**
 * Extension for shopping cart positions.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ShoppingCartPositionExtension extends DataExtension {
    
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'ProductAttributes' => 'Text',
    ];
    
    /**
     * Updates the CMS fields.
     * 
     * @param FieldList $fields Fields
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName('ProductAttributes');
    }
    
    /**
     * Returns the related variant attributes.
     * 
     * @return \SilverStripe\ORM\DataList
     */
    public function getVariantAttributes() {
        $attributeValues      = ArrayList::create();
        $serializedAttributes = $this->owner->ProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            foreach ($attributesArray as $ID => $attribute) {
                if (is_array($attribute)) {
                    unset($attributesArray[$ID]);
                }
            }
            $attributeValues = ProductAttributeValue::get()
                ->where('"' . ProductAttributeValue::config()->get('table_name') . '"."ID" IN (' . implode(',', $attributesArray) . ')');
        }
        return $attributeValues;
    }
    
    /**
     * Returns the related variant attributes.
     * 
     * @return DataList
     */
    public function getUserInputAttributes() {
        $attributeValues      = null;
        $userInputValues      = ArrayList::create();
        $serializedAttributes = $this->owner->ProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            $userInputAttributes = [];
            foreach ($attributesArray as $ID => $attribute) {
                if (is_array($attribute)) {
                    $userInputAttributes[$ID] = $attribute;
                }
            }
            $attributesArray = [];
            foreach ($userInputAttributes as $ID => $data) {
                $attributesArray[$ID] = $data['Option'];
            }
            if (count($attributesArray) > 0) {
                $idString = implode(',', $attributesArray);
                if (!empty($idString)) {
                    $attributeValues = ProductAttributeValue::get()
                        ->where('"' . ProductAttributeValue::config()->get('table_name') . '"."ID" IN (' . implode(',', $attributesArray) . ')');

                    foreach ($attributeValues as $value) {
                        if (!array_key_exists($value->ProductAttribute()->ID, $userInputAttributes)) {
                            continue;
                        }
                        $userInputValues->push(ArrayData::create([
                            'AttributeTitle' => $value->ProductAttribute()->Title,
                            'Title'          => $value->Title . ' "' . $userInputAttributes[$value->ProductAttribute()->ID]['TextValue'] . '"',
                            'ID'             => $value->ID,
                        ]));
                    }
                }
            }
            
            if (!($attributeValues instanceof SS_List) ||
                $attributeValues->count() < count($attributesArray)) {
                foreach ($userInputAttributes as $ID => $userInputAttribute) {
                    if (array_key_exists($ID, $userInputValues)) {
                        continue;
                    }
                    $attribute = ProductAttribute::get()->byID($ID);
                    $userInputValues->push(ArrayData::create([
                        'AttributeTitle' => $attribute->Title,
                        'Title'          => '"' . $userInputAttribute['TextValue'] . '"',
                        'ID'             => 0,
                    ]));
                }
            }
        }
        return $userInputValues;
    }

    /**
     * Overwrites the position price.
     *
     * @param DBMoney &$overwrittenPrice The price to set
     * @param boolean $forSingleProduct  Indicates wether the price for the total
     *                                   quantity of products should be returned
     *                                   or for one product only.
     * @param boolean $priceType         'gross' or 'net'. If undefined it'll be automatically chosen.
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function overwriteGetPrice(&$overwrittenPrice, $forSingleProduct, $priceType) {
        $product          = $this->owner->Product();
        $price            = $product->getPrice($priceType);
        $finalPriceAmount = 0;

        if ($product instanceof Product &&
            $product->exists()) {
            
            $variantAttributes = ArrayList::create($this->owner->getVariantAttributes()->toArray());
            $variantAttributes->merge($this->owner->getUserInputAttributes());
            foreach ($variantAttributes as $variantAttributeValue) {
                $productAttribute = $product->ProductAttributeValues()->byID($variantAttributeValue->ID);
                $priceAmount      = 0;
                if (!($productAttribute instanceof ProductAttributeValue) ||
                    !$productAttribute->exists()) {
                    continue;
                }
                if (!empty($productAttribute->ModifyPriceValue)) {
                    $priceAmount = MoneyField::create('tmp')->prepareAmount($productAttribute->ModifyPriceValue);
                }
                switch ($productAttribute->ModifyPriceAction) {
                    case 'add':
                        $finalPriceAmount = $price->getAmount() + $priceAmount;
                        break;
                    case 'subtract':
                        $finalPriceAmount = $price->getAmount() - $priceAmount;
                        break;
                    case 'setTo':
                        $finalPriceAmount = $priceAmount;
                        break;
                    default:
                        break;
                }
            }
        }
        
        if ($finalPriceAmount > 0) {
            if (!$forSingleProduct) {
                $finalPriceAmount = $finalPriceAmount * $this->owner->Quantity;
            }
            $overwrittenPrice = DBMoney::create()
                    ->setCurrency($price->getCurrency())
                    ->setAmount($finalPriceAmount);
        }
    }

    /**
     * Overwrites the ProductNumberShop.
     *
     * @param string &$productNumber The original product number
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function overwriteGetProductNumberShop(&$productNumber) {
        $product = $this->owner->Product();

        if ($product instanceof Product &&
            $product->exists()) {
            
            $variantAttributes = $this->owner->getVariantAttributes();
            foreach ($variantAttributes as $variantAttributeValue) {
                $productAttribute = $product->ProductAttributeValues()->byID($variantAttributeValue->ID);
                switch ($productAttribute->ModifyProductNumberAction) {
                    case 'add':
                        $productNumber .= $productAttribute->ModifyProductNumberValue;
                        break;
                    case 'setTo':
                        $productNumber = $productAttribute->ModifyProductNumberValue;
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Updates the title.
     *
     * @param string &$title The title to update
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function updateTitle(&$title) {
        $product = $this->owner->Product();

        if ($product instanceof Product &&
            $product->exists()) {
            
            $variantAttributes = $this->owner->getVariantAttributes();
            foreach ($variantAttributes as $variantAttributeValue) {
                $productAttribute = $product->ProductAttributeValues()->byID($variantAttributeValue->ID);
                switch ($productAttribute->ModifyTitleAction) {
                    case 'add':
                        $title .= $productAttribute->ModifyTitleValue;
                        break;
                    case 'setTo':
                        $title = $productAttribute->ModifyTitleValue;
                        break;
                    default:
                        break;
                }
            }
        }
    }
    
    /**
     * Adds some information to the title.
     *
     * @param string &$addToTitle String to add to title
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addToTitle(&$addToTitle) {
        $addToTitle .= (string) $this->owner->renderWith(static::class . '_AddToTitle');
    }
    
    /**
     * Adds some information to the title to display in widgets.
     *
     * @param string &$addToTitleForWidget String to add to title
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addToTitleForWidget(&$addToTitleForWidget) {
        $addToTitleForWidget .= (string) $this->owner->renderWith(static::class . '_AddToTitleForWidget');
    }
    
}