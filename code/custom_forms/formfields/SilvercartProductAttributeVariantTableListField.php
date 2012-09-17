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
 * @subpackage Deals FormFields
 */

/**
 * Modified TableListField to display variants
 *
 * @package Silvercart
 * @subpackage ProductAttributes FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.09.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeVariantTableListField extends TableListField {

    /**
     * Class name to use for the TableListFIelds items
     * 
     * @var $itemClass string Class name for each item/row
     */
    public $itemClass = 'SilvercartProductAttributeVariantTableListField_Item';
    
    /**
     * Field list vor variation data
     *
     * @var array
     */
    protected $variantFieldList = array();
    
    /**
     * ID list of selected items
     *
     * @var array
     */
    protected $selectedItems = array();

    /**
     * Constructor
     * 
     * @param SilvercartProduct $baseItem          Base item to get variation table for
     * @param string            $name              Name of the field
     * @param string            $addToFieldList    Additional fields to add to the default field list
     * @param string            $ignoreBaseItem    Ignore base item in item list?
     * @param string            $sourceFilter      Filter for the item list
     * @param string            $addToSourceFilter Additional filter for the item list
     * @param string            $sourceSort        Sort fields and direction of the items
     * @param string            $sourceJoin        Join database table
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function __construct($baseItem, $name, $addToFieldList = array(), $ignoreBaseItem = false, $sourceFilter = '', $addToSourceFilter = '', $sourceSort = null, $sourceJoin = null) {
        if ($baseItem->isMasterProduct()) {
            $master = $baseItem;
        } else {
            $master = $baseItem->SilvercartMasterProduct();
        }
        $sourceClass = 'SilvercartProduct';
        $fieldList = array_merge(
                array(
                    'ProductNumberShop' => $baseItem->fieldLabel('ProductNumberShop'),
                    'Title' => $baseItem->fieldLabel('Title'),
                    'SilvercartProductGroup.Title' => $baseItem->fieldLabel('SilvercartProductGroup'),
                    'isActiveString' => $baseItem->fieldLabel('isActive'),
                ),
                $addToFieldList
        );
        if (empty($sourceFilter)) {
            $sourceFilter = sprintf(
                    "(`SilvercartMasterProductID` = '%s' OR `SilvercartProduct`.`ID` = '%s')%s",
                    $master->ID,
                    $master->ID,
                    $addToSourceFilter
            );
        }
        if ($ignoreBaseItem) {
            $sourceFilter .= sprintf(
                " AND `SilvercartProduct`.`ID` != '%s'",
                $baseItem->ID
        );
        }
        parent::__construct($name, $sourceClass, $fieldList, $sourceFilter, $sourceSort, $sourceJoin);
    }

    /**
     * Adds the variation data to the headings and returns them
     * 
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function Headings() {
        $headings = parent::Headings();
        $variantAttributes = new DataObjectSet();
        $items = $this->sourceItems();
        if ($items) {
            foreach ($items as $item) {
                if ($item) {
                    $variantAttributes->merge($item->getVariantAttributes());
                }
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

        return $headings;
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
    
    /**
     * Returns the list of selected item IDs
     * 
     * @return array
     */
    public function getSelectedItems() {
        return $this->selectedItems;
    }

    /**
     * Sets the list of selected item IDs
     * 
     * @param array $selectedItems IDs of selected items as array
     * 
     * @return void
     */
    public function setSelectedItems($selectedItems) {
        $this->selectedItems = $selectedItems;
    }
    
    /**
     * Returns whether the given item ID is within the list of selected items
     * 
     * @param int $itemID ID of the item to check
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function isSelectedItem($itemID) {
        $isSelectedItem = false;
        if (in_array($itemID, $this->selectedItems)) {
            $isSelectedItem = true;
        }
        return $isSelectedItem;
    }

}

/**
 * Modified TableListField_Item to display variants
 *
 * @package Silvercart
 * @subpackage ProductAttributes FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.09.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeVariantTableListField_Item extends TableListField_Item {

    /**
     * Adds the variation data to the items fields and returns them
     * 
     * @param bool $xmlSafe Return the data XML safe?
     * 
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function Fields($xmlSafe = true) {
        $fields                         = parent::Fields($xmlSafe);
        $variantList                    = $this->parent->VariantFieldList();
        $variantAttributeValues         = $this->item->SilvercartProductAttributeValues();
        $variantAttributeValueGroups    = $variantAttributeValues->groupBy('SilvercartProductAttributeID');
        foreach ($variantList as $variantAttributeID => $variantAttributeTitle) {
            if (array_key_exists($variantAttributeID, $variantAttributeValueGroups)) {
                $fields->push(
                        new ArrayData(
                                array(
                                    "Name"          => 'SilvercartVariantAttribute' . $variantAttributeID,
                                    "Title"         => $variantAttributeTitle,
                                    "Value"         => implode(', ', $variantAttributeValueGroups[$variantAttributeID]->map('ID', 'Title')),
                                    "CsvSeparator"  => $this->parent->getCsvSeparator(),
                                )
                        )
                );
            }
        }
        return $fields;
    }

    /**
     * Returns the marking checkbox
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.09.2012
     */
    public function MarkingCheckbox() {
        $name       = $this->parent->Name() . '[]';
        $readOnly   = '';
        $selected   = '';
        if ($this->Parent()->isSelectedItem($this->ID())) {
            $selected = 'checked="checked"';
        }
        if ($this->parent->isReadonly()) {
            $readOnly = 'disabled="disabled"';
        }

        $markingCheckbox = sprintf(
                '<input class="checkbox" type="checkbox" name="%s" value="%s" %s %s />',
                $name,
                $this->item->ID,
                $readOnly,
                $selected
        );

        return $markingCheckbox;
    }

}