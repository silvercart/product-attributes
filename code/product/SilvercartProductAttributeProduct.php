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
 * Attribute set to collect attributes
 *
 * @package Silvercart
 * @subpackage Products
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeProduct extends DataObjectDecorator {
    
    /**
     * Adds som extra data model fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function extraStatics() {
        return array(
            'many_many'  => array(
                'SilvercartProductAttributes'       => 'SilvercartProductAttribute',
                'SilvercartProductAttributeValues'  => 'SilvercartProductAttributeValue',
            ),
        );
    }
    
    /**
     * Updates the CMS fields
     *
     * @param FieldSet &$fields Fields to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateCMSFields(FieldSet &$fields) {
        if ($this->owner->ID > 0) {
            if (SilvercartConfig::DisplayTypeOfProductAdminFlat()) {
                $tabPath = 'Root.SilvercartProductAttributes';
                $fields->removeByName('SilvercartProductAttributes');
                $fields->removeByName('SilvercartProductAttributeValues');
            } else {
                $tabPath = 'Root.Main.SilvercartProductAttributes';
            }
            $fields->findOrMakeTab($tabPath, _t('SilvercartProductAttribute.TABNAME'));
            $attributeField = new SilvercartProductAttributeTableListField($this->owner, 'SilvercartProductAttributes');
            $fields->addFieldToTab($tabPath, $attributeField);
        }
    }
    
    /**
     * Updates the field labels
     *
     * @param array &$labels Labels to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
                $labels,
                array(
                    'SilvercartProductAttributes'       => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTES'),
                    'SilvercartProductAttributeValues'  => _t('SilvercartProductAttributeProduct.PRODUCT_ATTRIBUTE_VALUES'),
                )
        );
    }
    
    /**
     * Updates the summary fields
     *
     * @param array &$fields Fields to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function updateSummaryFields(&$fields) {
        
    }
}

/**
 * The SilvercartProductAttributeProduct_RecordController record controller.
 *
 * @package Silvercart
 * @subpackage Backend
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 14.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeProduct_RecordController extends DataObjectDecorator {
    
    /**
     * Adds the abillity to execute additional actions to the model admin's
     * action handling.
     *
     * @param SS_HTTPRequest $request The request object
     * 
     * @return mixed
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function handleAction(SS_HTTPRequest $request) {
        $allRequestParams = $request->allParams();
        
        if (array_key_exists('ID', $allRequestParams)) {
            $action = str_replace('action_', '', $allRequestParams['ID']);
        }
        if (array_key_exists('OtherID', $allRequestParams)) {
            $otherId = $allRequestParams['OtherID'];
        }
        
        switch ($action) {
            case 'doSPAPAddUnAssignedAttribute':
                return $this->doAddUnAssignedAttribute($request, $otherId);
                break;
            case 'doSPAPRemoveAssignedAttribute':
                return $this->doRemoveAssignedAttribute($request, $otherId);
                break;
            case 'doSPAPAddUnAssignedValue':
                return $this->doAddUnAssignedValue($request, $otherId);
                break;
            case 'doSPAPRemoveAssignedValue':
                return $this->doRemoveAssignedValue($request, $otherId);
                break;
            default:
                return false;
        }
    }
    
    /**
     * Adds an unassigned attribute set to a product.
     *
     * @param SS_HTTPRequest $request     The request object
     * @param int            $attributeID The ID of the attributeset
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doAddUnAssignedAttribute(SS_HTTPRequest $request, $attributeID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attribute          = DataObject::get_by_id('SilvercartProductAttribute', $attributeID);
        
        if ($attribute) {
            if (!$silvercartProduct->SilvercartProductAttributes()->find('ID', $attribute->ID)) {
                $silvercartProduct->SilvercartProductAttributes()->add($attribute);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attribute->ID);
    }
    
    /**
     * Removes an assigned attribute set to a product.
     *
     * @param SS_HTTPRequest $request     The request object
     * @param int            $attributeID The ID of the attributed attribute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doRemoveAssignedAttribute(SS_HTTPRequest $request, $attributeID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attribute          = DataObject::get_by_id('SilvercartProductAttribute', $attributeID);
        
        if ($attribute) {
            $silvercartProduct->SilvercartProductAttributes()->remove($attribute);
            foreach ($silvercartProduct->SilvercartProductAttributeValues() as $value) {
                if ($value->SilvercartProductAttributeID == $attribute->ID) {
                    $silvercartProduct->SilvercartProductAttributeValues()->remove($value);
                    $silvercartProduct->write();
                }
            }
        }
        
        return $this->returnTableFieldForAjaxRequests();
    }
    
    /**
     * Adds an unassigned attribute value to a product.
     *
     * @param SS_HTTPRequest $request          The request object
     * @param int            $attributeValueID The ID of the attribute value
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doAddUnAssignedValue(SS_HTTPRequest $request, $attributeValueID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attributeValue     = DataObject::get_by_id('SilvercartProductAttributeValue', $attributeValueID);
        
        if ($attributeValue) {
            if (!$silvercartProduct->SilvercartProductAttributeValues()->find('ID', $attributeValue->ID)) {
                $silvercartProduct->SilvercartProductAttributeValues()->add($attributeValue);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attributeValue->SilvercartProductAttribute()->ID);
    }
    
    /**
     * Adds an unassigned attribute value to a product.
     *
     * @param SS_HTTPRequest $request          The request object
     * @param int            $attributeValueID The ID of the attribute value
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function doRemoveAssignedValue(SS_HTTPRequest $request, $attributeValueID) {
        $silvercartProduct  = $this->owner->currentRecord;
        $attributeValue     = DataObject::get_by_id('SilvercartProductAttributeValue', $attributeValueID);
        
        if ($attributeValue) {
            if ($silvercartProduct->SilvercartProductAttributeValues()->find('ID', $attributeValue->ID)) {
                $silvercartProduct->SilvercartProductAttributeValues()->remove($attributeValue);
            }
        }
        
        return $this->returnTableFieldForAjaxRequests($attributeValue->SilvercartProductAttribute()->ID);
    }
    
    /**
     * Returns the SilvercartProductAttributeTableListField for ajax requests as 
     * HTML code.
     *
     * @param int $activeAttributeID The ID of the active attribute
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    protected function returnTableFieldForAjaxRequests($activeAttributeID = null) {
        $data = SilvercartProductAttributeTableListField::getFieldData($this->owner->currentRecord, $activeAttributeID);
        return $this->owner->customise($data)->renderWith('SilvercartProductAttributeTableListField');
    }
}
