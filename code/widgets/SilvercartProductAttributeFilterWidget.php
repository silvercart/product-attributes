<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
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
 * @license see license file in modules root directory
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeFilterWidget extends SilvercartWidget {
    
    private static $db = array(
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
     * Returns the extra css classes.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.04.2013
     */
    public function ExtraCssClasses() {
        return $this->dbObject('ExtraCssClasses')->getValue() . ' silvercart-product-attribute-filter-widget';
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
     * @return FieldList
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
 * @license see license file in modules root directory
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeFilterWidget_Controller extends SilvercartWidget_Controller {
    
    /**
     * Possible filter attributes
     *
     * @var ArrayList 
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
     * @var ArrayList
     */
    protected $products = null;
    
    /**
     * ProductGroup Controller
     *
     * @var SilvercartProductGroupPage_Controller 
     */
    protected $productGroup = null;

    /**
     * Initializes the widget controller
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.09.2014
     */
    public function init() {
        parent::init();
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $this->setFormAction(Controller::curr()->Link() . 'SilvercartProductAttributeFilter');
            $attributes = new ArrayList();
            $products   = $this->getProducts();
            if ($products &&
                $products->Count() > 0) {
                $productIDs = implode(',', $products->map('ID','ID'));
                $attributeList = SilvercartProductAttribute::get()
                        ->where(
                                sprintf(
                                        '"SilvercartProductAttribute"."ID" IN (%s) AND "SilvercartProductAttribute"."CanBeUsedForFilterWidget" = 1',
                                        sprintf(
                                                "SELECT DISTINCT
                                                    SilvercartProductAttributeID
                                                FROM
                                                    SilvercartProduct_SilvercartProductAttributes
                                                WHERE
                                                    SilvercartProductID IN (%s)",
                                                $productIDs
                                        )
                                )
                        )->sort('"SilvercartProductAttributeLanguage"."Title" ASC');
                if ($attributeList->count() > 0) {
                    $attributeValues    = SilvercartProductAttributeValue::get()
                            ->where(
                                    sprintf(
                                            '"SilvercartProductAttributeValue"."ID" IN (%s)',
                                            sprintf(
                                                    "SELECT DISTINCT
                                                        SilvercartProductAttributeValueID
                                                    FROM
                                                        SilvercartProduct_SilvercartProductAttributeValues
                                                    WHERE
                                                        SilvercartProductID IN (%s)",
                                                    $productIDs
                                            )
                                    )
                            )->sort('"SilvercartProductAttributeID","SilvercartProductAttributeValueLanguage"."Title"');
                    if ($attributeValues) {
                        foreach ($attributeList as $attribute) {
                            $values = $attributeValues->filter('SilvercartProductAttributeID', $attribute->ID);
                            if ($values->count() > 0) {
                                $attribute->assignValues($values);
                                if (!(!$attribute->hasAssignedValues() ||
                                      ($attribute->getAssignedValues()->Count() == 1 &&
                                      !$attribute->getAssignedValues()->First()->IsFilterValue()))) {
                                    
                                    $attributes->push($attribute);
                                }
                            }
                        }
                    } else {
                        $attributes = new ArrayList();
                    }
                }
            }
            if ($attributes instanceof ArrayList) {
                $attributes = $attributes->sort(array(
                    'HasSelectedValues' => 'DESC',
                    'Title' => 'ASC',
                ));
            } else {
                $attributes = new ArrayList();
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
            !Controller::curr()->isProductDetailView() &&
            $this->getAttributes()->Count() > 0) {
            $content = trim(parent::Content());
        }
        return $content;
    }
    
    /**
     * Returns the attributes
     *
     * @return ArrayList 
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets the Attributes
     *
     * @param ArrayList $attributes Attributes
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
     * @return ArrayList
     */
    public function getProducts() {
        if (is_null($this->products)) {
            if (class_exists('SilvercartGroupBehaviorProductGroupPage_Controller')) {
                SilvercartGroupBehaviorProductGroupPage_Controller::$disable_filter = true;
            }
            if ($this->FilterBehaviour == 'MultipleChoice') {
                $products = new ArrayList(Controller::curr()->getUnfilteredProducts(false, false, true)->toArray());
                $products->merge(new ArrayList(Controller::curr()->getInjectedProducts(array('SilvercartProductAttributeFilterWidget'))->toArray()));
            } else {
                $products = new ArrayList(Controller::curr()->getProducts(false, false, true, true)->toArray());
                $products->merge(new ArrayList(Controller::curr()->getInjectedProducts(array('SilvercartProductAttributeFilterWidget'))->toArray()));
            }
            $this->products = $products;
            if (class_exists('SilvercartGroupBehaviorProductGroupPage_Controller')) {
                SilvercartGroupBehaviorProductGroupPage_Controller::$disable_filter = false;
            }
        }

        return $this->products;
    }

    /**
     * Returns the controller of the current product group
     *
     * @return SilvercartProductGroupPage_Controller
     */
    public function getProductGroup() {
        if (is_null($this->productGroup)) {
            $productGroup = Controller::curr();
            if (!$productGroup instanceof SilvercartProductGroupPage_Controller) {
                $productGroup = new SilvercartProductGroupPage_Controller();
            }
            $this->productGroup = $productGroup;
        }
        return $this->productGroup;
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
            $productMap             = $products->map('ID', 'LastEditedForCache');
            $productMapIDs          = implode('-', array_keys($productMap));
            sort($productMap);
            $productMapLastEdited   = array_pop($productMap);
            $attributesMapIDs       = '';
            $filterValueMapIDs      = $this->getProductGroup()->getFilterValueList();
            
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
                $filterValueMapIDs,
            );

            $key = implode('_', $keyParts);
        }
        return $key;
    }
}