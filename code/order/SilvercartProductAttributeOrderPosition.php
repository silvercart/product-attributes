<?php
/**
 * Copyright 2011 pixeltricks GmbH
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
 * @subpackage Order
 */

/**
 * Fetches additional stylesheets.
 *
 * @package SilvercartProductAttributes
 * @subpackage Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeOrderPosition extends DataObjectDecorator {
    
    /**
     * indicator whether the cms fields are already updated or not
     *
     * @var bool
     */
    protected $cmsFieldsUpdated = false;
    
    /**
     * Extends the database fields and relations of the decorated class.
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2012
     */
    public function extraStatics() {
        return array(
            'db' => array(
                'ProductAttributeVariantDefinition' => 'Text'
            )
        );
    }
    
    /**
     * CMS fields
     *
     * @param FieldSet $fields Fields to update
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.11.2012
     */
    public function updateCMSFields(FieldSet $fields) {
        if (!$this->cmsFieldsUpdated) {
            if ($this->owner->ID > 0) {
                
            }
            $this->cmsFieldsUpdated = true;
        }
    }

    /**
     * Field labels for display in tables.
     *
     * @param array &$labels Field labels to update
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.11.2012
     */
    public function updateFieldLabels(&$labels) {
        parent::updateFieldLabels($labels);
        $labels = array_merge(
            $labels,
            array(
                'ProductAttributeVariantDefinition' => _t('SilvercartProductAttributeOrderPosition.VARIANTDEFINITION'),
            )
        );
    }
}