<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

use SilverCart\Model\Order\ShoppingCartPosition;
use SilverCart\Model\Product\Product;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for shopping cart.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 * 
 * @property \SilverCart\Model\Order\ShoppingCart $owner Owner
 */
class ShoppingCartExtension extends DataExtension
{
    /**
     * The field handlers for processing special fields
     *
     * @var array
     */
    public static $registeredFieldHandlers = [];
    
    /**
     * Registers a field handler.
     *
     * @param mixed $handlerObject The handler object
     *
     * @return void
     */
    public static function registerFieldHandler($handlerObject) : void
    {
        self::$registeredFieldHandlers[] = $handlerObject;
    }
    
    /**
     * Overwrites the addProduct method.
     *
     * @param bool                 &$overwriteAddProduct Set to true if the addProduct routine was overwritten
     * @param array                $formData             Submitted form data
     * @param ShoppingCartPosition &$position            Shopping cart position
     * 
     * @return void
     */
    public function overwriteAddProduct(bool &$overwriteAddProduct, array $formData, ShoppingCartPosition &$position = null) : void
    {
        if (!array_key_exists('productID', $formData)
         && !array_key_exists('productQuantity', $formData)
        ) {
            return;
        }
        $productID       = $formData['productID'];
        $productQuantity = $formData['productQuantity'];
        $product         = Product::get()->byID($productID);
        if ($product instanceof Product
         && $product->exists()
         && $product->hasSingleProductVariants()
        ) {
            $overwriteAddProduct = true;
            $attributeValues     = [];
            foreach ($formData as $fieldName => $fieldValue) {
                if (strpos($fieldName, 'ProductAttribute') === 0
                 && !empty($fieldValue)
                ) {
                    $attributeID = str_replace('ProductAttribute', '', $fieldName);
                    $attributeValues[$attributeID] = $fieldValue;
                }
                foreach (self::$registeredFieldHandlers as $fieldHandler) {
                    if (method_exists($fieldHandler, 'processRequest')) {
                        $processedAttributes = $fieldHandler->processRequest();
                        if (!empty($processedAttributes)) {
                            foreach ($processedAttributes as $attributeID => $attributeID) {
                                $attributeValues[$attributeID] = $attributeID;
                            }
                        }
                    }
                }
            }
            $quantity = (float) str_replace(',', '.', $productQuantity);
            if ($quantity > 0) {
                $position = $product->SPAPaddToCartWithAttributes($this->owner->ID, $quantity, $attributeValues);
            }
        }
    }
}