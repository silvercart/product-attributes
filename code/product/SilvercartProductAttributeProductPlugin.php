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
     * @param DataObjectSet $variants Variants
     * 
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.08.2014
     */
    public function Headings($variants) {
        $product  = singleton('SilvercartProduct');
        $headings = new DataObjectSet();
        $headings->push(new ArrayData(array("Name" => 'ProductNumber', "Title" => $product->fieldLabel('ProductNumberShop'))));
        $headings->push(new ArrayData(array("Name" => 'Title',         "Title" => $product->fieldLabel('Title'))));
        
        $variantAttributes = new DataObjectSet();
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
     * @param DataObjectSet     $variants Variants
     * @param SilvercartProduct $original Original product
     * 
     * @return DataObjectSet
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
            $variant->Fields = $this->Fields($variant);
        }
        return $variants;
    }

    /**
     * Adds the variation data to the items fields and returns them
     * 
     * @param SilvercartProduct $product Product
     * 
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function Fields($product) {
        $fields = new DataObjectSet();
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
