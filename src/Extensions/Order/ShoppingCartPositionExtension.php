<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

use SilverCart\Admin\Model\Config;
use SilverCart\Forms\FormFields\MoneyField;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\FieldType\DBMoney;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;

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
        $attributeValues      = new ArrayList();
        $serializedAttributes = $this->owner->ProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            $attributeValues = ProductAttributeValue::get()
                ->where('"' . ProductAttributeValue::config()->get('table_name') . '"."ID" IN (' . implode(',', $attributesArray) . ')');
        }
        return $attributeValues;
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
            
            $variantAttributes = $this->owner->getVariantAttributes();
            foreach ($variantAttributes as $variantAttributeValue) {
                $productAttribute = $product->ProductAttributeValues()->byID($variantAttributeValue->ID);
                $priceAmount      = 0;
                if (!empty($productAttribute->ModifyPriceValue)) {
                    $priceAmount = $productAttribute->ModifyPriceValue;
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