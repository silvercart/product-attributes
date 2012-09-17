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
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage detail view.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.09.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeAddCartFormDetailPlugin extends DataObjectDecorator {
    
    /**
     * We inject our additional fields here.
     * 
     * @param array &$formFields    Form fields to manipulate
     * @param mixed &$callingObject The calling object
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function pluginUpdateFormFields(&$formFields, &$callingObject) {
        $product = Controller::curr()->detailViewProduct;
        if ($product->hasVariants()) {
            $callingObject->fieldGroups['SilvercartProductAttributes'] = $product->getVariantFormFields();
        }
    }
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function pluginAddCartFormDetailAdditionalFields(&$arguments, &$callingObject) {
        $product    = Controller::curr()->detailViewProduct;
        $output     = '';
        if ($product->hasVariants()) {
            $renderer           = new ViewableData();
            $templateData       = array(
                'Form'              => $callingObject,
                'Controller'        => Controller::curr(),
                'hasVariants'       => $product->hasVariants(),
                'SilvercartProduct' => $product
            );

            $output = $renderer->customise($templateData)->renderWith(
                'SilvercartProductAttributeAddCartFormDetailPlugin'
            );
        }
        return $output;
    }
}
