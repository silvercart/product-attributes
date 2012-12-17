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
 * @package Silvercart
 * @subpackage Products
 */

/**
 * Extension for products
 *
 * @package Silvercart
 * @subpackage Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 11.12.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeProductPlugin extends DataObjectDecorator {
    
    /**
     * Adds a tab for product attribute information information
     *
     * @param SilvercartProduct $callingObject Product to add tab for
     * 
     * @return DataObject 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.12.2012
     */
    public function pluginGetPluggedInTabs($callingObject) {
        $pluggedInTab = null;
        if ($callingObject->SilvercartProductAttributes()->Count() > 0 &&
            $callingObject->SilvercartProductAttributeValues()->Count() > 0) {
            $name       = _t('SilvercartProductAttribute.PLURALNAME');
            $content    = $callingObject->renderWith('SilvercartProductAttributeTab');
            if (!empty($content)) {
                $data = array(
                    'Name'      => $name,
                    'Content'   => $content,
                );
                $pluggedInTab = new DataObject($data);
            }
        }
        return $pluggedInTab;
    }
    
}
