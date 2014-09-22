<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 */

/**
 * Shopping cart position extension by SilvercartProductAttributes
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 17.09.2012
 * @license see license file in modules root directory
 */
class SilvercartProductAttributeOrderPositionPlugin extends DataExtension {
    
    /**
     * Adds a string to the positions title if related to a variant
     *
     * @param SilvercartShoppingCartPosition $position Position to extend title for
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 17.09.2012
     */
    public function pluginAddToTitle($position) {
        $addToTitle = '';
        if (!empty($position->ProductAttributeVariantDefinition)) {
            $addToTitle .= $position->ProductAttributeVariantDefinition;
        }
        return $addToTitle;
    }
    
}