<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 */

/**
 * Delivers additional functionality for SilvercartShoppingCartPosition.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license see license file in modules root directory
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeShoppingCartPositionPlugin extends DataExtension {
    
    /**
     * Prepares the given amount to save in db.
     * Makes som format corrections in case of wrong format
     *
     * @param string $amount Amount to check
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function prepareAmount($amount) {
        $val = array(
            'Amount'   => $amount,
            'Currency' => SilvercartConfig::DefaultCurrency(),
        );
        $moneyField = new SilvercartMoneyField('Dummy');
        $moneyField->setValue($val);
        $val = $moneyField->Value();
        return $val['Amount'];
    }

    /**
     * Overwrites the getPrice method.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object (SilvercartShoppingcartPosition)
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginOverwriteGetPrice(&$arguments, &$callingObject)
    {
        $product          = $callingObject->SilvercartProduct();
        $price            = $product->getPrice();
        $finalPriceAmount = $price->getAmount();
        $priceObj         = null;
        if ($product instanceof SilvercartProduct
         && $product->exists()
        ) {
            $variantAttributes = ArrayList::create($callingObject->getVariantAttributes()->toArray());
            $variantAttributes->merge($callingObject->getUserInputAttributes());
            if (!is_null($variantAttributes)
             && $variantAttributes->exists()
            ) {
                foreach ($variantAttributes as $variantAttributeValue) {
                    $productAttribute = $product->SilvercartProductAttributeValues()->byID($variantAttributeValue->ID);
                    $priceAmount      = 0;
                    if (!($productAttribute instanceof SilvercartProductAttributeValue)
                     || !$productAttribute->exists()
                    ) {
                        continue;
                    }
                    if (!empty($productAttribute->FinalModifyPriceValue)) {
                        $priceAmount = $this->prepareAmount($productAttribute->FinalModifyPriceValue);
                    }
                    switch ($productAttribute->FinalModifyPriceAction) {
                        case 'add':
                            $finalPriceAmount += $priceAmount;
                            break;
                        case 'subtract':
                            $finalPriceAmount -= $priceAmount;
                            break;
                        case 'setTo':
                            $finalPriceAmount = $priceAmount;
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        if ($finalPriceAmount > 0) {
            if (!$arguments[0]) {
                $finalPriceAmount = $finalPriceAmount * $callingObject->Quantity;
            }
            $priceObj = Money::create();
            $priceObj->setCurrency($price->getCurrency());
            $priceObj->setAmount($finalPriceAmount);
        }
        return $priceObj;
    }

    /**
     * Overwrites the getProductNumberShop method.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object (SilvercartShoppingcartPosition)
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginOverwriteGetProductNumberShop(&$arguments, &$callingObject) {
        $product       = $callingObject->SilvercartProduct();
        $productNumber = $product->ProductNumberShop;

        if ($product instanceof SilvercartProduct &&
            $product->exists()) {
            
            $variantAttributes = $callingObject->getVariantAttributes();
            if (!is_null($variantAttributes) &&
                $variantAttributes->exists()) {
                
                foreach ($variantAttributes as $variantAttributeValue) {
                    $productAttribute = $product->SilvercartProductAttributeValues()->byID($variantAttributeValue->ID);
                    switch ($productAttribute->FinalModifyProductNumberAction) {
                        case 'add':
                            $productNumber .= $productAttribute->FinalModifyProductNumberValue;
                            break;
                        case 'setTo':
                            $productNumber = $productAttribute->FinalModifyProductNumberValue;
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        return $productNumber;
    }

    /**
     * Overwrites the getTitle method.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object (SilvercartShoppingcartPosition)
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginOverwriteGetTitle(&$arguments, &$callingObject) {
        $product = $callingObject->SilvercartProduct();
        $title   = $product->Title;

        if ($product instanceof SilvercartProduct &&
            $product->exists()) {
            
            $variantAttributes = $callingObject->getVariantAttributes();
            if (!is_null($variantAttributes) &&
                $variantAttributes->exists()) {
                
                foreach ($variantAttributes as $variantAttributeValue) {
                    $productAttribute = $product->SilvercartProductAttributeValues()->byID($variantAttributeValue->ID);
                    switch ($productAttribute->FinalModifyTitleAction) {
                        case 'add':
                            $title .= $productAttribute->FinalModifyTitleValue;
                            break;
                        case 'setTo':
                            $title = $productAttribute->FinalModifyTitleValue;
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        return $title == $product->Title ? '' : $title;
    }
    
    /**
     * Returns an extension for the shopping cart position title
     *
     * @param SilvercartShoppingCartPosition $position Position to add title extension
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function pluginAddToTitle($position) {
        return $position->renderWith('SilvercartProductAttributeShoppingCartPositionAddToTitle');
    }
    
    /**
     * Returns an extension for the shopping cart position title
     *
     * @param SilvercartShoppingCartPosition $position Position to add title extension
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function pluginAddToTitleForWidget($position) {
        return $position->renderWith('SilvercartProductAttributeShoppingCartPositionAddToTitleForWidget');
    }
}
