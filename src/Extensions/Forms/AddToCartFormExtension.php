<?php

namespace SilverCart\ProductAttributes\Extensions\Forms;

use SilverCart\Model\Product\Product;
use SilverStripe\Core\Extension;

/**
 * Delivers additional information for the AddToCartForm.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class AddToCartFormExtension extends Extension {
    
    /**
     * Updates the default form fields.
     * 
     * @param array &$fields Form fields to update
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function updateCustomFields(&$fields) {
        $product = $this->owner->getProduct();

        if ($product instanceof Product &&
            $product->exists()) {

            if ($product->hasVariants()) {
                $fields = array_merge(
                        $fields,
                        $product->getVariantFormFields()
                );
            }
            if ($product->hasSingleProductVariants()) {
                $fields = array_merge(
                        $fields,
                        $product->getSingleProductVariantFormFields()
                );
            }
        }
    }
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     * 
     * @param string &$rendered Rendered form fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function renderUpdatedCustomFields(&$rendered) {
        $product = $this->owner->getProduct();
        
        if (($product instanceof Product &&
             $product->exists()) &&
            ($product->hasVariants() ||
             $product->hasSingleProductVariants())) {

            $rendered .= $this->owner->renderWith(static::class . '_' . $this->owner->getViewContext());
        }
    }
    
}