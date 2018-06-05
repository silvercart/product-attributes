<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

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
 */
class ShoppingCartExtension extends DataExtension {
    
    /**
     * The field handlers for processing special fields
     *
     * @var array
     */
    public static $registeredFieldHandlers = array();
    
    /**
     * Registers a field handler.
     *
     * @param mixed $handlerObject The handler object
     *
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public static function registerFieldHandler($handlerObject) {
        self::$registeredFieldHandlers[] = $handlerObject;
    }
    
    /**
     * Overwrites the addProduct method.
     *
     * @param boolean &$overwriteAddProduct Set to true if the addProduct routine was overwritten
     * @param array   $formData             Submitted form data
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function overwriteAddProduct(&$overwriteAddProduct, $formData) {
        if (!array_key_exists('productID', $formData) &&
            !array_key_exists('productQuantity', $formData)) {
            return;
        }
        $productID       = $formData['productID'];
        $productQuantity = $formData['productQuantity'];
        $product         = Product::get()->byID($productID);
        if ($product instanceof Product &&
            $product->exists() &&
            $product->hasSingleProductVariants()) {
            
            $overwriteAddProduct = true;
            $attributeValues     = array();

            foreach ($formData as $fieldName => $fieldValue) {
                if (strpos($fieldName, 'ProductAttribute') === 0 &&
                    !empty($fieldValue)) {

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
                $product->SPAPaddToCartWithAttributes($this->owner->ID, $quantity, $attributeValues);
            }
        }
    }
}
