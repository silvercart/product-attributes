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
 * Adds information to newly created order positions.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeOrderPlugin extends DataObjectDecorator {

    /**
     * This method gets called while the SilvercartShoppingCartPositions are
     * converted to SilvercartOrderPositions.
     *
     * @param array &$shoppingCartPosition The SilvercartShoppingCartPosition
     * @param array &$orderPosition        The SilvercartOrderPosition
     * @param mixed &$callingObject        The calling object
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function pluginConvertShoppingCartPositionToOrderPosition(&$shoppingCartPosition, &$orderPosition, &$callingObject) {
        $productAttributeVariantDefinition  = '';
        $variantAttributeValues             = $shoppingCartPosition->SilvercartProduct()->getVariantAttributeValues();
        foreach ($variantAttributeValues as $variantAttributeValue) {
            if (!empty($productAttributeVariantDefinition)) {
                $productAttributeVariantDefinition .= ', ';
            }
            $productAttributeVariantDefinition .= '<em>' . $variantAttributeValue->SilvercartProductAttribute()->Title . '</em>: ' . $variantAttributeValue->Title;
        }

        $orderPosition->ProductAttributeVariantDefinition = $productAttributeVariantDefinition;
    }
}
