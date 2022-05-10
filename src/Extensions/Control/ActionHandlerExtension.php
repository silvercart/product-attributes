<?php

namespace SilverCart\ProductAttributes\Extensions\Control;

use SilverCart\Model\Product\Product;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;

/**
 * Extension for SilverCart ActionHandler.
 * 
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.01.2022
 * @copyright 2022 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property \SilverCart\Control\ActionHandler $owner Owner
 */
class ActionHandlerExtension extends Extension
{
    /**
     * List of allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'choose_product_attribute',
        'load_product_id',
        'reload_product_attribute_nav_item',
        'reset_product_attribute',
    ];
    
    /**
     * Chooses a product attribute value and redirects back or returns an AJAX response.
     * 
     * @param HTTPRequest $request HTTP request
     * 
     * @return HTTPResponse
     */
    public function choose_product_attribute(HTTPRequest $request) : HTTPResponse
    {
        $chosenValue = ProductAttributeValue::get()->byID($request->param('ID'));
        /* @var $chosenValue ProductAttributeValue */
        if ($chosenValue === null
         || (!$chosenValue->ProductAttribute()->ShowAsNavigationItem
          && !$chosenValue->ProductAttribute()->RequestInProductGroups)
        ) {
            $this->owner->httpError(400, 'bad request.');
        }
        $added = $chosenValue->chooseGlobally();
        if ($request->isAjax()) {
            return HTTPResponse::create(json_encode([
                'Added'       => $added,
                'HTMLNavItem' => (string) $chosenValue->ProductAttribute()->forTemplate('HeaderNavItem'),
                'URLSegment'  => (string) $chosenValue->ProductAttribute()->URLSegment,
            ]));
        } else {
            return $this->owner->redirectBack();
        }
    }
    
    /**
     * Laods a product ID.
     * 
     * @param HTTPRequest $request HTTP request
     * 
     * @return HTTPResponse
     */
    public function load_product_id(HTTPRequest $request) : HTTPResponse
    {
        $product           = Product::get()->byID($request->postVar('productID'));
        $variantAttributes = [];
        foreach ($request->postVars() as $name => $value) {
            if (strpos($name, 'ProductAttribute') !== 0) {
                continue;
            }
            $id = str_replace('ProductAttribute', '', $name);
            $variantAttributes[(int) $value] = (int) $value;
        }
        if ($product instanceof Product 
         && !empty($variantAttributes)
        ) {
            $variant = $product->getVariantBy($variantAttributes);
            if ($variant) {
                if ($request->isAjax()) {
                    return HTTPResponse::create($variant->ID);
                } else {
                    return $this->owner->redirectBack();
                }
            }
        }
        return $this->owner->httpError(400, 'bad request.');
    }
    
    /**
     * Reloads the product attribute nav item.
     * 
     * @param HTTPRequest $request HTTP request
     * 
     * @return HTTPResponse
     */
    public function reload_product_attribute_nav_item(HTTPRequest $request) : HTTPResponse
    {
        $global = ProductAttribute::getGlobal();
        /* @var $chosenValue ProductAttributeValue */
        if ($global === null) {
            $this->owner->httpError(400, 'bad request.');
        }
        if ($request->isAjax()) {
            return HTTPResponse::create($global->forTemplate('HeaderNavItem'));
        } else {
            return $this->owner->redirectBack();
        }
    }
    
    /**
     * Chooses a product attribute value and redirects back or returns an AJAX response.
     * 
     * @param HTTPRequest $request HTTP request
     * 
     * @return HTTPResponse
     */
    public function reset_product_attribute(HTTPRequest $request) : HTTPResponse
    {
        $attribute = ProductAttribute::get()->byID($request->param('ID'));
        /* @var $attribute ProductAttribute */
        if ($attribute === null
         || (!$attribute->ShowAsNavigationItem
          && !$attribute->RequestInProductGroups)
        ) {
            $this->owner->httpError(400, 'bad request.');
        }
        $chosen = ProductAttribute::getGloballyChosen();
        if (array_key_exists($attribute->ID, $chosen)) {
            unset($chosen[$attribute->ID]);
            ProductAttribute::setGloballyChosen($chosen);
        }
        if ($request->isAjax()) {
            return HTTPResponse::create(json_encode([
                'Added'       => false,
                'HTMLNavItem' => (string) $attribute->forTemplate('HeaderNavItem'),
                'URLSegment'  => (string) $attribute->URLSegment,
            ]));
        } else {
            return $this->owner->redirectBack();
        }
    }
}