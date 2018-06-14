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
        $attributeValues      = ArrayList::create();
        $serializedAttributes = $this->owner->SilvercartProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            foreach ($attributesArray as $ID => $attribute) {
                if (is_array($attribute)) {
                    unset($attributesArray[$ID]);
                }
            }
            $attributeValues = SilvercartProductAttributeValue::get()
                ->where('"SilvercartProductAttributeValue"."ID" IN (' . implode(',', $attributesArray) . ')');
        }
        return $attributeValues;
    }
    
    /**
     * Returns the related variant attributes.
     * 
     * @return DataList
     */
    public function getUserInputAttributes() {
        $attributeValues      = null;
        $userInputValues      = ArrayList::create();
        $serializedAttributes = $this->owner->SilvercartProductAttributes;
        $attributesArray      = unserialize($serializedAttributes);
        if (is_array($attributesArray) &&
            count($attributesArray) > 0) {
            
            $userInputAttributes = [];
            foreach ($attributesArray as $ID => $attribute) {
                if (is_array($attribute)) {
                    $userInputAttributes[$ID] = $attribute;
                }
            }
            $attributesArray = [];
            foreach ($userInputAttributes as $ID => $data) {
                $attributesArray[$ID] = $data['Option'];
            }
            if (count($attributesArray) > 0) {
                $idString = implode(',', $attributesArray);
                if (!empty($idString)) {
                    $attributeValues = SilvercartProductAttributeValue::get()
                        ->where('"SilvercartProductAttributeValue"."ID" IN (' . implode(',', $attributesArray) . ')');

                    foreach ($attributeValues as $value) {
                        if (!array_key_exists($value->SilvercartProductAttribute()->ID, $userInputAttributes)) {
                            continue;
                        }
                        $userInputValues->push(ArrayData::create([
                            'AttributeTitle' => $value->SilvercartProductAttribute()->Title,
                            'Title'          => $value->Title . ' "' . $userInputAttributes[$value->SilvercartProductAttribute()->ID]['TextValue'] . '"',
                            'ID'             => $value->ID,
                        ]));
                    }
                }
            }
            
            if (!($attributeValues instanceof SS_List) ||
                $attributeValues->count() < count($attributesArray)) {
                foreach ($userInputAttributes as $ID => $userInputAttribute) {
                    if (array_key_exists($ID, $userInputValues)) {
                        continue;
                    }
                    $attribute = SilvercartProductAttribute::get()->byID($ID);
                    $userInputValues->push(ArrayData::create([
                        'AttributeTitle' => $attribute->Title,
                        'Title'          => '"' . $userInputAttribute['TextValue'] . '"',
                        'ID'             => 0,
                    ]));
                }
            }
        }
        return $userInputValues;
    }
}
