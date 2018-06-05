<?php

namespace SilverCart\ProductAttributes\Admin\Dev;

use SilverCart\Model\Product\Product;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeTranslation;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValueTranslation;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DB;

/**
 * Class to import variants out of the obsolete product variant module.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Admin_Dev
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @todo check or delete.
 */
class ProductVariantImporter {
    
    /**
     * Maps the ID of an attribute to an obsolete variant attribute set.
     *
     * @var array
     */
    protected $importAttributeMap = [];
    
    /**
     * Maps the ID of an attribute value to an obsolete variant attribute.
     *
     * @var array
     */
    protected $importAttributeValueMap = [];
    
    /**
     * Maps the ID of an attribute to an obsolete attributed variant attribute set.
     *
     * @var array
     */
    protected $importAttributeSetProductMap = [];
    
    /**
     * Optional MySQL table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';
    
    /**
     * Optional MySQL table prefix.
     *
     * @var string
     */
    protected $tableName = 'SilvercartProductVariantAttribute';

    /**
     * Executes the attribute import from the obsolete variant module.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function doImport() {
        $doImport  = false;
        $results   = DB::query('SHOW TABLES');
        
        foreach ($results as $table) {
            if (in_array($this->tableName, $table) ||
                in_array(strtolower($this->tableName), $table) ||
                in_array(strtoupper($this->tableName), $table)) {
                $doImport = true;
                break;
            }
            if (in_array('_obsolete_' . $this->tableName, $table) ||
                in_array(strtolower('_obsolete_' . $this->tableName), $table) ||
                in_array(strtoupper('_obsolete_' . $this->tableName), $table)) {
                $doImport = true;
                $this->tablePrefix = '_obsolete_';
                break;
            }
        }
        if ($doImport) {
            $this->importProductVariantAttributeSets();
            $this->importProductVariantAttributeSets(true, true);
            $this->importProductVariantAttributes();
            $this->importProductVariantAttributes(true, true);
            $this->importProductVariantAttributeRelations(true);
            $this->importProductVariantProductRelations(true);
        }
    }

    /**
     * Imports the product variant modules (obsolete) variants.
     * 
     * @param bool $forTranslations Execute import for translations
     * @param bool $renameTable     Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributeSets($forTranslations = false, $renameTable = false) {
        $whereClause = '"VASL"."Locale" = \'' . i18n::get_locale() . '\'';
        if ($forTranslations) {
            $whereClause = '"VASL"."Locale" != \'' . i18n::get_locale() . '\'';
        }
        $attributeSets = DB::query('SELECT * FROM "' . $this->tablePrefix . $this->tableName . 'Set" AS "VAS" LEFT JOIN "' . $this->tablePrefix . $this->tableName . 'SetLanguage" AS "VASL" ON ("VAS"."ID" = "VASL"."' . $this->tableName . 'SetID") WHERE ' . $whereClause);
        if ($attributeSets->numRecords() > 0) {
            foreach ($attributeSets as $attributeSet) {
                if (array_key_exists($attributeSet[$this->tableName . 'SetID'], $this->importAttributeMap)) {
                    $existingAttribute = ProductAttribute::get()->byID($this->importAttributeMap[$attributeSet[$this->tableName . 'SetID']]);
                } else {
                    $existingAttribute = ProductAttribute::get()->filter('Title', $attributeSet['name'])->first();
                }
                if (is_null($existingAttribute)) {
                    $existingAttribute = new ProductAttribute();
                    $existingAttribute->Title                        = $attributeSet['name'];
                    $existingAttribute->IsUserInputField             = $attributeSet['type'] == 'userInput';
                    $existingAttribute->UserInputFieldMustBeFilledIn = $attributeSet['mustBeFilledIn'] == '1';
                    $existingAttribute->CanBeUsedForSingleVariants   = true;
                    $existingAttribute->write();
                }
                if ($forTranslations &&
                    !$existingAttribute->hasTranslation($attributeSet['Locale'])) {
                    $translation = new ProductAttributeTranslation();
                    $translation->Locale                       = $attributeSet['Locale'];
                    $translation->Title                        = $attributeSet['name'];
                    $translation->ProductAttributeID = $existingAttribute->ID;
                    $translation->write();
                }
                $this->importAttributeMap[$attributeSet[$this->tableName . 'SetID']] = $existingAttribute->ID;
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . $this->tableName . 'Set" TO "_imported_' . $this->tableName . 'Set"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . $this->tableName . 'SetLanguage" TO "_imported_' . $this->tableName . 'SetLanguage"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variants.
     * 
     * @param bool $forTranslations Execute import for translations
     * @param bool $renameTable     Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributes($forTranslations = false, $renameTable = false) {
        $whereClause = '"VAL"."Locale" = \'' . i18n::get_locale() . '\'';
        if ($forTranslations) {
            $whereClause = '"VAL"."Locale" != \'' . i18n::get_locale() . '\'';
        }
        $attributes = DB::query('SELECT * FROM "' . $this->tablePrefix . $this->tableName . 'Language" AS "VAL" WHERE ' . $whereClause);
        if ($attributes->numRecords() > 0) {
            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute[$this->tableName . 'ID'], $this->importAttributeValueMap)) {
                    $existingAttributeValue = ProductAttributeValue::get()->byID($this->importAttributeValueMap[$attribute[$this->tableName . 'ID']]);
                } else {
                    $existingAttributeValue = ProductAttributeValue::get()->filter('Title', $attribute['name'])->first();
                }
                if (is_null($existingAttributeValue)) {
                    $existingAttributeValue = new ProductAttributeValue();
                    $existingAttributeValue->Title                        = $attribute['name'];
                    $existingAttributeValue->write();
                }
                if ($forTranslations &&
                    !$existingAttributeValue->hasTranslation($attribute['Locale'])) {
                    $translation = new ProductAttributeValueTranslation();
                    $translation->Locale                            = $attribute['Locale'];
                    $translation->Title                             = $attribute['name'];
                    $translation->ProductAttributeValueID = $existingAttributeValue->ID;
                    $translation->write();
                }
                $this->importAttributeValueMap[$attribute[$this->tableName . 'ID']] = $existingAttributeValue->ID;
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . $this->tableName . '" TO "_imported_' . $this->tableName . '"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . $this->tableName . 'Language" TO "_imported_' . $this->tableName . 'Language"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant relations.
     * 
     * @param bool $renameTable Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantAttributeRelations($renameTable = false) {
        $attributeRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . $this->tableName . 'Set_Attributes" AS "VASA"');
        if ($attributeRelations->numRecords() > 0) {
            foreach ($attributeRelations as $attributeRelation) {
                if (array_key_exists($attributeRelation[$this->tableName . 'SetID'], $this->importAttributeMap) &&
                    array_key_exists($attributeRelation[$this->tableName . 'ID'], $this->importAttributeValueMap)) {
                    $attributeValue = ProductAttributeValue::get()->byID($this->importAttributeValueMap[$attributeRelation[$this->tableName . 'ID']]);
                    if ($attributeValue->ProductAttributeID == 0) {
                        $attributeValue->ProductAttributeID = $this->importAttributeMap[$attributeRelation[$this->tableName . 'SetID']];
                        $attributeValue->write();
                    } elseif ($attributeValue->ProductAttributeID != $this->importAttributeMap[$attributeRelation[$this->tableName . 'SetID']]) {
                        $newAttributeValue = $attributeValue->duplicate();
                        $newAttributeValue->ProductAttributeID = $this->importAttributeMap[$attributeRelation[$this->tableName . 'SetID']];
                        $newAttributeValue->write();
                    }
                }
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . $this->tableName . 'Set_Attributes" TO "_imported_' . $this->tableName . 'Set_Attributes"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant relations.
     * 
     * @param bool $renameTable Rename obsolete database tables after import
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function importProductVariantProductRelations($renameTable = false) {
        $attributeSetProductRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet" AS "AVAS"');
        $products = [];
        if ($attributeSetProductRelations->numRecords() > 0) {
            foreach ($attributeSetProductRelations as $attributeSetProductRelation) {
                $productID  = $attributeSetProductRelation['SilvercartProductID'];
                $attributeD = $this->importAttributeMap[$attributeSetProductRelation[$this->tableName . 'SetID']];
                $product    = Product::get()->byID($productID);
                $attribute  = ProductAttribute::get()->byID($attributeD);
                $this->importAttributeSetProductMap[$attributeSetProductRelation['ID']] = $productID;
                $products[$productID] = $product;
                if ($product instanceof Product &&
                    $product->exists()) {
                    $product->ProductAttributes()->add($attribute);
                }
            }
        }
        
        $attributeProductRelations = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet_Attributes" AS "AVASA"');
        if ($attributeProductRelations->numRecords() > 0) {
            foreach ($attributeProductRelations as $attributeProductRelation) {
                if (array_key_exists($attributeProductRelation['SilvercartAttributedVariantAttributeSetID'], $this->importAttributeSetProductMap) &&
                    array_key_exists($attributeProductRelation[$this->tableName . 'ID'], $this->importAttributeValueMap)) {
                    $productID        = $this->importAttributeSetProductMap[$attributeProductRelation['SilvercartAttributedVariantAttributeSetID']];
                    $attributeValueID = $this->importAttributeValueMap[$attributeProductRelation[$this->tableName . 'ID']];
                    
                    if (array_key_exists($productID, $products)) {
                        $product = $products[$productID];
                    } else {
                        $product = Product::get()->byID($productID);
                    }
                    if ($product instanceof Product &&
                        $product->exists()) {
                        $fieldModifiers = $this->getProductVariantAttributeFieldModifiers($attributeProductRelation[$this->tableName . 'ID'], $attributeProductRelation['SilvercartAttributedVariantAttributeSetID']);
                        $attributeValue = ProductAttributeValue::get()->byID($attributeValueID);
                        $product->ProductAttributeValues()->add($attributeValue, array_merge([
                            'IsActive'  => $attributeProductRelation['isActive'],
                            'IsDefault' => $attributeProductRelation['isDefault'],
                        ], $fieldModifiers));
                    }
                }
            }
        }
        if ($renameTable) {
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet" TO "_imported_SilvercartAttributedVariantAttributeSet"');
            DB::query('RENAME TABLE "' . $this->tablePrefix . 'SilvercartAttributedVariantAttributeSet_Attributes" TO "_imported_SilvercartAttributedVariantAttributeSet_Attributes"');
        }
    }
    
    /**
     * Imports the product variant modules (obsolete) variant field modifiers.
     * 
     * @param int   $attributeID                     Attribute ID
     * @param array $attributedVariantAttributeSetID Set ID
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    protected function getProductVariantAttributeFieldModifiers($attributeID, $attributedVariantAttributeSetID) {
        $fieldModifiers = [];
        $attributeFieldModifiers = DB::query('SELECT * FROM "' . $this->tablePrefix . 'SilvercartProductVariantFieldModifier" AS "PVFM" WHERE "PVFM"."' . $this->tableName . 'ID" = ' . $attributeID . ' AND "PVFM"."SilvercartAttributedVariantAttributeSetID" = ' . $attributedVariantAttributeSetID);
        if ($attributeFieldModifiers->numRecords() > 0) {
            foreach ($attributeFieldModifiers as $attributeFieldModifier) {
                if ($attributeFieldModifier['silvercartProductFieldName'] == 'Title') {
                    $fieldModifiers['ModifyTitleAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyTitleValue']  = $attributeFieldModifier['modifierValue'];
                } elseif ($attributeFieldModifier['silvercartProductFieldName'] == 'PriceGrossAmount') {
                    $fieldModifiers['ModifyPriceAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyPriceValue']  = $attributeFieldModifier['modifierValue'];
                } elseif ($attributeFieldModifier['silvercartProductFieldName'] == 'ProductNumberShop') {
                    $fieldModifiers['ModifyProductNumberAction'] = $attributeFieldModifier['modifierAction'];
                    $fieldModifiers['ModifyProductNumberValue']  = $attributeFieldModifier['modifierValue'];
                }
            }
        }
        return $fieldModifiers;
    }
    
}