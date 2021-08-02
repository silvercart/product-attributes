<?php

namespace SilverCart\ProductAttributes\Admin\Controllers;

use SilverCart\Admin\Controllers\ModelAdmin;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeSet;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;

/**
 * ModelAdmin for ProductAttribute.
 * 
 * @package SilverCart
 * @subpackage ProductAttributes_Admin_Controllers
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 */
class ProductAttributeAdmin extends ModelAdmin
{
    /**
     * The code of the menu under which this admin should be shown.
     * 
     * @var string
     */
    private static $menuCode = 'products';
    /**
     * The section of the menu under which this admin should be grouped.
     * 
     * @var string
     */
    private static $menuSortIndex = 100;
    /**
     * The URL segment
     *
     * @var string
     */
    private static $url_segment = 'silvercart-product-attributes';
    /**
     * The menu title
     *
     * @var string
     */
    private static $menu_title = 'Product Attributes';
    /**
     * Managed models
     *
     * @var array
     */
    private static $managed_models = [
        ProductAttribute::class,
        ProductAttributeValue::class,
        ProductAttributeSet::class,
    ];
    /**
     * Name of DB field to make records sortable by.
     *
     * @var string
     */
    private static $sortable_field = 'Sort';
}