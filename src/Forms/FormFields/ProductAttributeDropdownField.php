<?php

namespace SilverCart\ProductAttributes\Forms\FormFields;

use SilverCart\Dev\Tools;
use SilverStripe\Forms\DropdownField;
use SilverStripe\View\ArrayData;

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
class ProductAttributeDropdownField extends DropdownField
{
    use ProductAttributeFormField;
    
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
    protected $hasEmptyDefault = false;

    /**
     * Allows customization through an 'updateAttributes' hook on the base class.
     * Existing attributes are passed in as the first argument and can be manipulated,
     * but any attributes added through a subclass implementation won't be included.
     * 
     * Adds the data-action attribute.
     *
     * @return array
     */
    public function getAttributes() : array
    {
        return array_merge(
                parent::getAttributes(),
                $this->getProductAttributeAttributes()
        );
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
    protected function getFieldOption($value, $title) : ArrayData
    {
        $option = parent::getFieldOption($value, $title);
        $this->addPricesToOption($option, $value);
        return $option;
    }
}