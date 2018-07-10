<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

/**
 * Extension for SilverCart PageController.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class PageControllerExtension extends Extension {
    
    /**
     * Adds some JS files.
     * 
     * @param array &$jsFiles JS files
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function updateRequireExtendedJavaScript(&$jsFiles) {
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
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function onAfterInit() {
        Requirements::themedCss('client/css/ProductAttribute');
        Requirements::themedCss('client/css/PriceRangeFormField');
    }
    
}