<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
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
 * @license see license file in modules root directory
 */
class SilvercartProductAttributeProductPlugin extends DataExtension {
    
    /**
     * Field list vor variation data
     *
     * @var array
     */
    protected $variantFieldList = array();
    
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
        if ($callingObject->SilvercartProductAttributes()->filter('CanBeUsedForDataSheet', true)->count() > 0 &&
            $callingObject->SilvercartProductAttributeValues()->count() > 0) {
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
    
    /**
     * Adds some information to display between Images and Content.
     *
     * @param SilvercartProduct $callingObject Product to add data for
     * 
     * @return DataObject 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.08.2014
     */
    public function pluginGetPluggedInAfterImageContent($callingObject) {
        $pluggedInContent = null;
        if ($callingObject->hasVariants()) {
            
            $customisedData = array(
                'Headings' => $this->Headings($callingObject->getVariants()),
                'Items'    => $this->Items($callingObject->getVariants(), $callingObject),
            );
            $name    = _t('SilvercartProductAttribute.PLURALNAME');
            $content = $callingObject->customise($customisedData)->renderWith('SilvercartProductAttributeVariantTable');
            if (!empty($content)) {
                $data = array(
                    'Content' => $content,
                );
                $pluggedInContent = new DataObject($data);
            }
        }
        return $pluggedInContent;
    }
    
    /**
     * Adds the variation data to the headings and returns them
     * 
     * @param DataList $variants Variants
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.08.2014
     */
    public function Headings($variants) {
        $product  = singleton('SilvercartProduct');
        $headings = new ArrayList();
        $headings->push(new ArrayData(array("Name" => 'ProductNumber', "Title" => $product->fieldLabel('ProductNumberShop'))));
        $headings->push(new ArrayData(array("Name" => 'Title',         "Title" => $product->fieldLabel('Title'))));
        
        $variantAttributes = new ArrayList();
        foreach ($variants as $item) {
            if ($item) {
                $variantAttributes->merge($item->getVariantAttributes());
            }
        }
        $variantAttributes->removeDuplicates();

        foreach ($variantAttributes as $attribute) {
            $this->variantFieldList[$attribute->ID] = $attribute->Title;
            $headings->push(
                    new ArrayData(
                            array(
                                "Name" => 'SilvercartVariantAttribute' . $attribute->ID,
                                "Title" => $attribute->Title,
                                "IsSortable" => false,
                                "SortLink" => false,
                                "SortBy" => false,
                                "SortDirection" => null,
                            )
                    )
            );
        }
        
        $headings->push(new ArrayData(array("Name" => 'Price', "Title" => $product->fieldLabel('Price'))));

        return $headings;
    }
    
    /**
     * Returns the items.
     * 
     * @param ArrayList         $variants Variants
     * @param SilvercartProduct $original Original product
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.08.2014
     */
    public function Items($variants, $original) {
        if (!$original->IsNotBuyable) {
            $variants->push($original);
        }
        $variants->sort('Title');
        foreach ($variants as $variant) {
            if ($variant->IsNotBuyable) {
                $variants->remove($variant);
            }
            $variant->Fields = $this->Fields($variant);
        }
        return $variants;
    }

    /**
     * Adds the variation data to the items fields and returns them
     * 
     * @param SilvercartProduct $product Product
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function Fields($product) {
        $fields = new ArrayList();
        $fields->push(new ArrayData(array("Name" => 'ProductNumber', "Value" => $product->ProductNumberShop, "Link" => $product->Link())));
        $fields->push(new ArrayData(array("Name" => 'Title',         "Value" => $product->Title,             "Link" => $product->Link())));
        
        $variantList                    = $this->VariantFieldList();
        $variantAttributeValues         = $product->SilvercartProductAttributeValues();
        $variantAttributeValueGroups    = $variantAttributeValues->groupBy('SilvercartProductAttributeID');
        foreach ($variantList as $variantAttributeID => $variantAttributeTitle) {
            if (array_key_exists($variantAttributeID, $variantAttributeValueGroups)) {
                $fields->push(
                        new ArrayData(
                                array(
                                    "Name"  => 'SilvercartVariantAttribute' . $variantAttributeID,
                                    "Value" => implode(', ', $variantAttributeValueGroups[$variantAttributeID]->map('ID', 'Title')),
                                    "Link"  => $product->Link()
                                )
                        )
                );
            }
        }
        
        $fields->push(new ArrayData(array("Name" => 'Price', "Value" => $product->getPriceNice(), "Link" => $product->Link())));
        
        return $fields;
    }
    
    /**
     * Returns the variant field list to use for the items
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function VariantFieldList() {
        return $this->variantFieldList;
    }
    
}
