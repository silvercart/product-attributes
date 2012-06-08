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
 * @subpackage Rating
 */

/**
 * Controller to load requirements
 * 
 * @package Silvercart
 * @subpackage Rating
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 24.05.2012 
 * @copyright 2012 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributePage_Controller extends DataObjectDecorator {
    
    /**
     * gets called before init() and registers the rating form if we're on a product detail
     * view 
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.05.2012 
     */
    public function onBeforeInit() {
        $baseUrl = SilvercartTools::getBaseURLSegment();
        $requirements = array(
            $baseUrl . 'silvercart_product_attributes/css/SilvercartProductAttributePriceRangeFormField.css',
        );
        RequirementsEngine::combine_files_and_parse('silvercart_product_attributes.css', $requirements);
        SilvercartCustomerRating::loadRequirements();
    }
    
}