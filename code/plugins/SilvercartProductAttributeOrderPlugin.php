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
 * Adds information to newly created order positions.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license see license file in modules root directory
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeOrderPlugin extends DataExtension {

    /**
     * This method gets called while the SilvercartShoppingCartPositions are
     * converted to SilvercartOrderPositions.
     *
     * @param array &$shoppingCartPosition The SilvercartShoppingCartPosition
     * @param array &$orderPosition        The SilvercartOrderPosition
     * @param mixed &$callingObject        The calling object
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.06.2018
     */
    public function pluginConvertShoppingCartPositionToOrderPosition(&$shoppingCartPosition, &$orderPosition, &$callingObject) {
        $productAttributeVariantDefinition  = '';
        $variantAttributeValues             = $shoppingCartPosition->getVariantAttributes();
        $userInputAttributeValues           = $shoppingCartPosition->getUserInputAttributes();
        foreach ($variantAttributeValues as $variantAttributeValue) {
            if (!empty($productAttributeVariantDefinition)) {
                $productAttributeVariantDefinition .= ', ';
            }
            $productAttributeVariantDefinition .= '<em>' . $variantAttributeValue->SilvercartProductAttribute()->Title . '</em>: ' . $variantAttributeValue->Title;
        }
        foreach ($userInputAttributeValues as $userInputAttributeValue) {
            if (!empty($productAttributeVariantDefinition)) {
                $productAttributeVariantDefinition .= ', ';
            }
            $productAttributeVariantDefinition .= '<em>' . $userInputAttributeValue->AttributeTitle . '</em>: ' . $userInputAttributeValue->Title;
        }

        $orderPosition->ProductAttributeVariantDefinition = $productAttributeVariantDefinition;
    }
}
