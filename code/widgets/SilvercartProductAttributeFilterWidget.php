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
 * @subpackage Widgets
 */

/**
 * Provides a view of items of a definable productgroup.
 *
 * @package Silvercart
 * @subpackage Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeFilterWidget extends SilvercartWidget {
    
    public static $db = array(
        'FilterBehaviour'   => 'Enum("MultipleChoice,SingleChoice","MultipleChoice")',
        'RememberFilter'    => 'Boolean(0)',
    );


    /**
     * Returns the title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function Title() {
        return _t('SilvercartProductAttributeFilterWidget.TITLE');
    }
    
    /**
     * Returns the title of this widget for display in the WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function CMSTitle() {
        return _t('SilvercartProductAttributeFilterWidget.CMSTITLE');
    }
    
    /**
     * Returns the description of what this template does for display in the
     * WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function Description() {
        return _t('SilvercartProductAttributeFilterWidget.DESCRIPTION');
    }
    
    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $content = parent::Content();
        }
        return $content;
    }

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function fieldLabels($includerelations = true) {
        return array_merge(
                parent::fieldLabels($includerelations),
                array(
                    'FilterBehaviour'   => _t($this->ClassName() . '.FILTERBEHAVIOUR'),
                    'RememberFilter'    => _t($this->ClassName() . '.REMEMBERFILTER'),
                )
        );
    }
    
    /**
     * CMS fields for this widget
     *
     * @return Fieldset
     */
    public function getCMSFields() {
        $fields                 = parent::getCMSFields();
        $filterBehaviourDbField = $this->dbObject('FilterBehaviour');
        $enumValues             = $filterBehaviourDbField->enumValues();
        $items                  = array();
        foreach ($enumValues as $key => $value) {
            $items[$key] = _t($this->ClassName() . '.FB_' . strtoupper($value), $value);
        }
        $filterBehaviourField = new OptionsetField('FilterBehaviour', $this->fieldLabel('FilterBehaviour'), $items, $this->FilterBehaviour);
        $filterBehaviourField->setRightTitle(_t($this->ClassName() . '.FB_HINT'));
        $fields->push($filterBehaviourField);
        
        $rememberFilterField = new CheckboxField('RememberFilter', $this->fieldLabel('RememberFilter'));
        $fields->push($rememberFilterField);
        return $fields;
    }
}

/**
 * Provides grouped filters to display in product group.
 *
 * @package Silvercart
 * @subpackage Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeFilterWidget_Controller extends SilvercartWidget_Controller {
    
    /**
     * Possible filter attributes
     *
     * @var DataObjectSet 
     */
    protected $attributes = null;
    
    /**
     * Form action for filter form
     *
     * @var string
     */
    protected $formAction = null;
    
    /**
     * Product list
     *
     * @var DataObjectSet
     */
    protected $products = null;

    /**
     * Initializes the widget controller
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012 
     */
    public function init() {
        parent::init();
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $this->setFormAction(Controller::curr()->Link() . 'SilvercartProductAttributeFilter');
            $attributes = new DataObjectSet();
            $products   = $this->getProducts();
            if ($products &&
                $products->Count() > 0) {
                $productIDs = implode(',', $products->map('ID','ID'));
                $attributeIDs = array();
                $records = DB::query(
                    sprintf(
                        "SELECT DISTINCT
                            SilvercartProductAttributeID
                        FROM
                            SilvercartProduct_SilvercartProductAttributes
                        WHERE
                            SilvercartProductID IN (%s)",
                        $productIDs
                    )
                );

                foreach ($records as $record) {
                    $attributeIDs[] = $record['SilvercartProductAttributeID'];
                }
                if (count($attributeIDs) > 0) {
                    $attributes = DataObject::get(
                            'SilvercartProductAttribute',
                            sprintf(
                                    "`SilvercartProductAttribute`.`ID` IN (%s)",
                                    implode(',', $attributeIDs)
                            )
                    );
                    $records = DB::query(
                        sprintf(
                            "SELECT DISTINCT
                                SilvercartProductAttributeValueID
                            FROM
                                SilvercartProduct_SilvercartProductAttributeValues
                            WHERE
                                SilvercartProductID IN (%s)",
                            $productIDs
                        )
                    );
                    $attributeValueIDs = array();
                    foreach ($records as $record) {
                        $attributeValueIDs[] = $record['SilvercartProductAttributeValueID'];
                    }
                    if (empty($attributeValueIDs)) {
                        $attributes = new DataObjectSet();
                    } else {
                        $attributeValues = DataObject::get(
                                'SilvercartProductAttributeValue',
                                sprintf(
                                        "`SilvercartProductAttributeValue`.`ID` IN (%s)",
                                        implode(',', $attributeValueIDs)
                                ),
                                "`SilvercartProductAttributeID`, `SilvercartProductAttributeValueLanguage`.`Title`"
                        );
                        $attributeValuesArray = $attributeValues->groupBy('SilvercartProductAttributeID');
                        foreach ($attributeValuesArray as $attributeID => $groupedAttributeValues) {
                            $attribute = $attributes->find('ID', $attributeID);
                            if ($attribute) {
                                $attribute->assignValues($groupedAttributeValues);
                                if (!$attribute->hasAssignedValues() ||
                                    ($attribute->getAssignedValues()->Count() == 1 &&
                                    !$attribute->getAssignedValues()->First()->IsFilterValue())) {
                                    $attributes->remove($attribute);
                                }
                            }
                        }
                    }
                }
            }
            if ($attributes instanceof DataObjectSet) {
                $groupedAttributes = $attributes->groupBy('HasSelectedValues');
                krsort($groupedAttributes);
                $attributes = new DataObjectSet();
                foreach ($groupedAttributes as $groupedAttribute) {
                    $groupedAttribute->sort('Title DESC');
                    $groupedAttribute->sort('Title ASC');
                    $attributes->merge($groupedAttribute);
                }
            }
            $this->setAttributes($attributes);
        }
    }

    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $content = parent::Content();
        }
        return $content;
    }
    
    /**
     * Returns the attributes
     *
     * @return DataObjectSet 
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets the Attributes
     *
     * @param DataObjectSet $attributes Attributes
     * 
     * @return void
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }
    
    /**
     * Returns the form action
     *
     * @return string
     */
    public function getFormAction() {
        return $this->formAction;
    }
    
    /**
     * Sets the form action
     *
     * @param string $formAction form action
     * 
     * @return void
     */
    public function setFormAction($formAction) {
        $this->formAction = $formAction;
    }

    /**
     * Returns all filter relevant products
     *
     * @return DataObjectSet
     */
    public function getProducts() {
        if (is_null($this->products)) {
            $products = new DataObjectSet();
            if ($this->FilterBehaviour == 'MultipleChoice') {
                $products = Controller::curr()->getUnfilteredProducts(false, false, true);
                $products->merge(Controller::curr()->getInjectedProducts());
            } else {
                $products = Controller::curr()->getProducts(false, false, true);
                $products->merge(Controller::curr()->getInjectedProducts());
            }
            $this->products = $products;
        }
        return $this->products;
    }

    /**
     * Creates the cache key for this widget.
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.07.2012
     */
    public function WidgetCacheKey() {
        $key        = '';
        $products   = $this->getProducts();
        $attributes = $this->getAttributes();
        
        if ($products->Count() > 0) {
            $productMap             = $products->map('ID', 'LastEdited');
            $productMapIDs          = implode('-', array_keys($productMap));
            sort($productMap);
            $productMapLastEdited   = array_pop($productMap);
            $attributesMapIDs       = '';
            
            if ($attributes->Count() > 0) {
                $attributesMap      = $attributes->map('ID', 'ID');
                $attributesMapIDs   = implode('-', $attributesMap);
            }
            
            $keyParts = array(
                i18n::get_locale(),
                $productMapIDs,
                $productMapLastEdited,
                $this->LastEdited,
                $attributesMapIDs,
            );

            $key = implode('_', $keyParts);
        }
        return $key;
    }
}