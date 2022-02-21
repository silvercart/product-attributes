<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverCart\Model\Pages\CartPageController;
use SilverCart\Model\Pages\CheckoutStepController;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataList;
use SilverStripe\View\Requirements;

/**
 * Extension for SilverCart PageController.
 *
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class PageControllerExtension extends Extension
{
    /**
     * Allowed actions.
     * 
     * @var string[]
     */
    private static $allowed_actions = [
        'modalChooseProductAttribute',
    ];
    
    /**
     * Action to return the content to display inside the choose-product-attribute
     * modal.
     * 
     * @param HTTPRequest $request HTTP request
     * 
     * @return HTTPResponse
     */
    public function modalChooseProductAttribute(HTTPRequest $request) : HTTPResponse
    {
        if (!$this->ProductAttributeNavigationItems()->exists()) {
            $this->owner->httpError(400);
        }
        return HTTPResponse::create($this->owner->renderWith(self::class . '_ModalChooseProductAttribute'));
    }
    
    /**
     * Adds some JS files.
     * 
     * @param array &$jsFiles JS files
     * 
     * @return void
     */
    public function updateRequireExtendedJavaScript(array &$jsFiles) : void
    {
        $jsFiles = array_merge(
            $jsFiles,
            [
                'silvercart/product-attributes:client/js/ProductAttribute.js',
                'silvercart/product-attributes:client/js/ProductAttributeFilterWidget.js',
                'silvercart/product-attributes:client/js/ProductAttributeDropdownField.js',
            ]
        );
    }
    
    /**
     * Adds some CSS requirements.
     * 
     * @return void
     */
    public function onAfterInit() : void
    {
        Requirements::themedCss('client/css/ProductAttribute');
        Requirements::themedCss('client/css/PriceRangeFormField');
    }
    
    /**
     * Updates the custom HTML content to add to footer.
     * 
     * @param string &$html HTML to update
     * 
     * @return void
     */
    public function updateFooterCustomHtmlContent(string &$html) : void
    {
        if ($this->owner instanceof CartPageController
         || $this->owner instanceof CheckoutStepController
        ) {
            return;
        }
        $html .= $this->owner->renderWith(self::class . '_FooterCustomHtmlContent');
    }
    
    /**
     * Returns all product attributes to show as navigation item.
     * 
     * @return DataList
     */
    public function ProductAttributeNavigationItems() : DataList
    {
        return ProductAttribute::getGlobals();
    }
    
    /**
     * Returns the first product attribute to show as navigation item.
     * 
     * @return DataList
     */
    public function ProductAttributeNavigationItem() : ?ProductAttribute
    {
        return ProductAttribute::getGlobal();
    }
}