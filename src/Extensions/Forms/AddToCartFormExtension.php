<?php

namespace SilverCart\ProductAttributes\Extensions\Forms;

use SilverCart\Control\ActionHandler;
use SilverCart\ProductAttributes\Extensions\Pages\PageControllerExtension;
use SilverCart\Model\Product\Product;
use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;

/**
 * Delivers additional information for the AddToCartForm.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 * 
 * @property \SilverCart\Forms\AddToCartForm $owner Owner
 */
class AddToCartFormExtension extends Extension
{
    /**
     * Updates the default form fields.
     * 
     * @param array &$fields Form fields to update
     * 
     * @return void
     */
    public function updateCustomFields(array &$fields) : void
    {
        $product = $this->owner->getProduct();
        if ($product instanceof Product
         && $product->exists()
        ) {
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
    public function renderUpdatedCustomFields(&$rendered) : void
    {
        $product = $this->owner->getProduct();
        if (($product instanceof Product
          && $product->exists())
         && ($product->hasVariants()
          || $product->hasSingleProductVariants())
        ) {
            $rendered .= $this->owner->renderWith(static::class . "_{$this->owner->getViewContext()}");
            PageControllerExtension::addProductAddCartFormModalFor($this->owner);
        }
    }
    
    /**
     * Returns the load_product_id action link
     * 
     * @return string
     */
    public function ProductAttributeLoadProductIDLink() : string
    {
        return Director::makeRelative(ActionHandler::config()->url_segment . '/load-product-id');
    }
}