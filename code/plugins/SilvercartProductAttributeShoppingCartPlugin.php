<?php
/**
 * Copyright 2016 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 */

/**
 * Delivers additional information for SilvercartShoppingCart.
 *
 * @package SilvercartProductVariants
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 07.11.2016
 * @license see license file in modules root directory
 * @copyright 2016 pixeltricks GmbH
 */
class SilvercartProductAttributeShoppingCartPlugin extends DataExtension {
    
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
     * @param array &$arguments     The arguments to pass
     *                              $arguments[0]: formData
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginOverwriteAddProduct(&$arguments, &$callingObject) {
        $overwritten = false;
        if ($arguments[0]['productID'] && $arguments[0]['productQuantity']) {
            $member = SilvercartCustomer::currentUser();
            if (!($member instanceof Member) ||
                !$member->exists()) {
                $member = SilvercartCustomer::createAnonymousCustomer();
            }
            $cart = $member->getCart();
            if ($cart instanceof SilvercartShoppingCart &&
                $cart->exists()) {
                $product = SilvercartProduct::get()->byID($arguments[0]['productID']);
                if ($product->hasSingleProductVariants()) {
                    $overwritten     = true;
                    $attributeValues = array();

                    foreach ($arguments[0] as $fieldName => $fieldValue) {
                        if (strpos($fieldName, 'SilvercartProductAttribute') === 0 &&
                            !empty($fieldValue)) {
                            
                            $attributeID = str_replace('SilvercartProductAttribute', '', $fieldName);
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

                    if ($product instanceof SilvercartProduct &&
                        $product->exists()) {
                        $arguments[0]['productQuantity'] = str_replace(',', '.', $arguments[0]['productQuantity']);
                        $quantity                        = (float) $arguments[0]['productQuantity'];

                        if ($quantity > 0) {
                            $product->SPAPaddToCartWithAttributes($cart->ID, $quantity, $attributeValues);
                        }
                    }
                }
            }
        }
        
        return $overwritten;
    }
}
