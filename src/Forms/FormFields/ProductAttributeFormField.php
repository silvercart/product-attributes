<?php

namespace SilverCart\ProductAttributes\Forms\FormFields;

use SilverStripe\Control\Controller;
use SilverStripe\View\ArrayData;

/**
 * Trait to add ProductAttribute related FormField features to a form field.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Forms_FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 13.06.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
trait ProductAttributeFormField {
    
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
     * Returns the data attributes for action, prices, type and product ID.
     * 
     * @return array
     */
    public function getProductAttributeAttributes() {
        return [
            'data-action'     => Controller::curr()->data()->Link() . '/' . ProductAttributeDropdownField::config()->get('load_variant_action'),
            'data-prices'     => $this->getProductPrices(),
            'data-type'       => $this->getProductVariantType(),
            'data-product-id' => $this->getProductID(),
        ];
    }
    
    /**
     * Adds the data-prices to the given option.
     * 
     * @param ArrayData &$option Option
     * @param mixed     $value   Value of the option
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.06.2018
     */
    public function addPricesToOption(ArrayData &$option, $value) {
        $jsonPrices = $this->getAttribute('data-prices');
        $prices     = json_decode($jsonPrices, true);
        if (is_array($prices)) {
            $price = 0;
            if (array_key_exists($value, $prices)) {
                $price = $prices[$value];
            }
            $option->Price = (string) $price;
        }
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