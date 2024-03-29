<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverCart\Forms\AddToCartForm;
use SilverCart\Model\Pages\CartPageController;
use SilverCart\Model\Pages\CheckoutStepController;
use SilverCart\Model\Pages\RegistrationPageController;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\Security\Security;
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
     * Adds a $form entry to the modal list.
     * 
     * @param AddToCartForm $form Form to add modal for
     * 
     * @return void
     */
    public static function addProductAddCartFormModalFor(AddToCartForm $form) : void
    {
        if (self::$productAddCartFormModals === null) {
            self::$productAddCartFormModals = ArrayList::create();
        }
        self::$productAddCartFormModals->push($form);
    }
    
    /**
     * Allowed actions.
     * 
     * @var string[]
     */
    private static $allowed_actions = [
        'modalChooseProductAttribute',
    ];
    /**
     * Product add cart form modals.
     * 
     * @var ArrayList|null
     */
    protected static $productAddCartFormModals = null;
    
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
     * Checks the HTTP GET parameters for product attributes.
     * 
     * @return void
     */
    public function onBeforeInit() : void
    {
        if (array_key_exists('scpa', $_GET)
         && is_array($_GET['scpa'])
        ) {
            foreach ($_GET['scpa'] as $attributeSegment => $valueSegment) {
                $attribute = ProductAttribute::getByURLSegment($attributeSegment);
                if ($attribute instanceof ProductAttribute) {
                    $value = ProductAttributeValue::getByURLSegment($valueSegment, ['ProductAttributeID' => $attribute->ID]);
                    if ($value instanceof ProductAttributeValue
                     && !$value->IsGloballyChosen()
                    ) {
                        $value->chooseGlobally();
                    }
                }
            }
        }
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
         || $this->owner instanceof RegistrationPageController
         || ($this->owner->getRequest()->param('Action')
          && strpos($this->owner->getRequest()->getURL(), 'Security/login') !== false)
        ) {
            $html .= $this->owner->renderWith(self::class . '_FooterCustomHtmlContentCheckout');
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

    /**
     * Returns the product add cart form modals.
     * 
     * @return ArrayList|null
     */
    public function ProductAddCartFormModals() : ?ArrayList
    {
        return self::$productAddCartFormModals;
    }
}