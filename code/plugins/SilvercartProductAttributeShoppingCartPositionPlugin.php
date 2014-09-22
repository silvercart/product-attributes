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
 * Delivers additional functionality for SilvercartShoppingCartPosition.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license see license file in modules root directory
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeShoppingCartPositionPlugin extends DataExtension {
    
    /**
     * Returns an extension for the shopping cart position title
     *
     * @param SilvercartShoppingCartPosition $position Position to add title extension
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function pluginAddToTitle($position) {
        return $position->renderWith('SilvercartProductAttributeShoppingCartPositionAddToTitle');
    }
    
    /**
     * Returns an extension for the shopping cart position title
     *
     * @param SilvercartShoppingCartPosition $position Position to add title extension
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function pluginAddToTitleForWidget($position) {
        return $position->renderWith('SilvercartProductAttributeShoppingCartPositionAddToTitleForWidget');
    }
}
