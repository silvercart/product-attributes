<?php
/**
 * Copyright 2016 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Order
 */

/**
 * Attaches new attributes.
 *
 * @package SilvercartProductAttributes
 * @subpackage Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 07.11.2016
 * @license see license file in modules root directory
 * @copyright 2016 pixeltricks GmbH
 */
class SilvercartProductAttributeShoppingCartPosition extends DataExtension {
    
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = array(
        'SilvercartProductAttributes' => 'Text',
    );
    
    /**
     * Updates the CMS fields.
     * 
     * @param FieldList $fields Fields
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName('SilvercartProductAttributes');
    }
    
    /**
     * Returns the related variant attributes.
     * 
     * @return DataList
     */
    public function getVariantAttributes() {
        $attributeValues      = null;
        $serializedAttributes = $this->owner->SilvercartProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            $attributeValues = SilvercartProductAttributeValue::get()
                ->where('"SilvercartProductAttributeValue"."ID" IN (' . implode(',', $attributesArray) . ')');
        }
        return $attributeValues;
    }
}
