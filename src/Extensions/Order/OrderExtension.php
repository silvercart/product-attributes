<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

use SilverCart\Model\Order\OrderPosition;
use SilverCart\Model\Order\ShoppingCartPosition;
use SilverStripe\ORM\DataExtension;

/**
 * Adds information to newly created order positions.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class OrderExtension extends DataExtension {

    /**
     * This method gets called while the ShoppingCartPositions are converted to 
     * OrderPositions.
     *
     * @param ShoppingCartPosition &$shoppingCartPosition The SilverCart ShoppingCartPosition
     * @param OrderPosition        &$orderPosition        The SilverCart OrderPosition
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function onBeforeConvertSingleShoppingCartPositionToOrderPosition(ShoppingCartPosition &$shoppingCartPosition, OrderPosition &$orderPosition) {
        $productAttributeVariantDefinition = '';
        $variantAttributeValues            = $shoppingCartPosition->getVariantAttributes();
        $userInputAttributeValues          = $shoppingCartPosition->getUserInputAttributes();
        foreach ($variantAttributeValues as $variantAttributeValue) {
            if (!empty($productAttributeVariantDefinition)) {
                $productAttributeVariantDefinition .= ', ';
            }
            $productAttributeVariantDefinition .= '<em>' . $variantAttributeValue->ProductAttribute()->Title . '</em>: ' . $variantAttributeValue->Title;
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
