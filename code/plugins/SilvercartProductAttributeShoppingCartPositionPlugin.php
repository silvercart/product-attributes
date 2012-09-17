<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeShoppingCartPositionPlugin extends DataObjectDecorator {
    
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
