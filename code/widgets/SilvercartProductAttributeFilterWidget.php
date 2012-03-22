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
                    'FilterBehaviour' => _t($this->ClassName() . '.FILTERBEHAVIOUR'),
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
            if ($this->FilterBehaviour == 'MultipleChoice') {
                $products = Controller::curr()->getUnfilteredProducts(false, false, true);
            } else {
                $products = Controller::curr()->getProducts(false, false, true);
            }
            $productIDs = implode(',', $products->map('ID','ID'));
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
            $attributes = DataObject::get(
                    'SilvercartProductAttribute',
                    sprintf(
                            "`ID` IN (%s)",
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
            foreach ($records as $record) {
                $attributeValueIDs[] = $record['SilvercartProductAttributeValueID'];
            }
            $attributeValues = DataObject::get(
                    'SilvercartProductAttributeValue',
                    sprintf(
                            "`ID` IN (%s)",
                            implode(',', $attributeValueIDs)
                    ),
                    "`SilvercartProductAttributeID`"
            );
            $attributeValuesArray = $attributeValues->groupBy('SilvercartProductAttributeID');
            foreach ($attributeValuesArray as $attributeID => $groupedAttributeValues) {
                $attribute = $attributes->find('ID', $attributeID);
                if ($attribute) {
                    $attribute->assignValues($groupedAttributeValues);
                    if (!$attribute->hasAssignedValues()) {
                        $attributes->remove($attribute);
                    }
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
}