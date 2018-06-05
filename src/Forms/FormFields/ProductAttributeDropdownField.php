<?php

namespace SilverCart\ProductAttributes\Forms\FormFields;

use SilverCart\Dev\Tools;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;

/**
 * A dropdown field to choose a product attribute as a product variation.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Forms_FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 04.06.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductAttributeDropdownField extends DropdownField {
    
    const VARIANT_TYPE_SINGLE = 'single-variant';
    const VARIANT_TYPE_MULTIPLE = '';
    
    /**
     * Target data action to load the variants.
     *
     * @var string
     */
    private static $load_variant_action = 'LoadVariant';

    /**
     * Show the first <option> element as empty (not having a value),
     * with an optional label defined through {@link $emptyString}.
     * By default, the <select> element will be rendered with the
     * first option from {@link $source} selected.
     *
     * @var bool
     */
    protected $hasEmptyDefault = true;
    
    /**
     * Product ID
     *
     * @var int
     */
    protected $productID = null;
    
    /**
     * JSON encoded list of prices.
     *
     * @var string
     */
    protected $productPrices = null;
    
    /**
     * Type of product variation (single or multiple products)
     *
     * @var string 
     */
    protected $productVariantType = null;

    /**
     * Allows customization through an 'updateAttributes' hook on the base class.
     * Existing attributes are passed in as the first argument and can be manipulated,
     * but any attributes added through a subclass implementation won't be included.
     * 
     * Adds the data-action attribute.
     *
     * @return array
     */
    public function getAttributes() {
        $attributes = array_merge(
                parent::getAttributes(),
                [
                    'data-action'     => Controller::curr()->data()->Link() . '/' . self::config()->get('load_variant_action'),
                    'data-prices'     => $this->getProductPrices(),
                    'data-type'       => $this->getProductVariantType(),
                    'data-product-id' => $this->getProductID(),
                ]
        );
        
        return $attributes;
    }
    
    /**
     * Build a field option for template rendering
     * 
     * Adds the product price to the option.
     * The price should be rendered as <em>data-price</em> attribute.
     *
     * @param mixed  $value Value of the option
     * @param string $title Title of the option
     * 
     * @return ArrayData Field option
     */
    protected function getFieldOption($value, $title) {
        $option = parent::getFieldOption($value, $title);
        
        $jsonPrices = $this->getAttribute('data-prices');
        $prices     = json_decode($jsonPrices, true);
        if (is_array($prices)) {
            $price = 0;
            if (array_key_exists($value, $prices)) {
                $price = $prices[$value];
            }
            $option->Price = (string) $price;
        }
        
        return $option;
    }

    /**
     * Returns the empty string to use as default dropdown option.
     * 
     * @return string
     */
    public function getEmptyString() {
        if (empty($this->emptyString)) {
            $this->setEmptyString(Tools::field_label('PleaseChoose'));
        }
        return $this->emptyString;
    }

    /**
     * Returns the product ID.
     * 
     * @return int
     */
    public function getProductID() {
        return $this->productID;
    }

    /**
     * Returns the JSON encoded product prices.
     * 
     * @return string
     */
    public function getProductPrices() {
        return $this->productPrices;
    }

    /**
     * Returns the product variant type.
     * 
     * @return string
     */
    public function getProductVariantType() {
        if (empty($this->productVariantType)) {
            $this->setProductVariantType(self::VARIANT_TYPE_MULTIPLE);
        }
        return $this->productVariantType;
    }

    /**
     * Sets the product ID.
     * 
     * @param int $productID Product ID
     * 
     * @return $this
     */
    public function setProductID($productID) {
        $this->productID = $productID;
        return $this;
    }

    /**
     * Sets the product prices (JSON encoded).
     * <code>
     * {[
     *      <PRODUCT-1-ID>: <PRODUCT-1-PRICE>,
     *      <PRODUCT-2-ID>: <PRODUCT-2-PRICE>,
     *      <PRODUCT-3-ID>: <PRODUCT-3-PRICE>,
     *      ...
     *      <PRODUCT-X-ID>: <PRODUCT-X-PRICE>
     * ]}
     * </code>
     * 
     * @param string $productPrices JSON encoded prices.
     * 
     * @return $this
     */
    public function setProductPrices($productPrices) {
        $this->productPrices = $productPrices;
        return $this;
    }

    /**
     * Sets the product variant type.
     * 
     * @param string $productVariantType Product variant type
     * 
     * @return $this
     * 
     * @see self::VARIANT_TYPE_SINGLE
     * @see self::VARIANT_TYPE_MULTIPLE
     */
    public function setProductVariantType($productVariantType) {
        $this->productVariantType = $productVariantType;
        return $this;
    }
    
}