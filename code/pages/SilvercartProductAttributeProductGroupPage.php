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
 * @subpackage Pages
 */

/**
 * Decorator for the SilvercartProductGroupPage_Controller.
 *
 * @package Silvercart
 * @subpackage Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeProductGroupPage_Controller extends DataObjectDecorator {
    
    protected $filterEnabled                = true;
    
    protected $filterDisabledPermanently    = false;

    protected $filterValues                 = null;
    
    protected $widget                       = null;

    public static $allowed_actions          = array(
        'ClearSilvercartProductAttributeFilter',
        'SilvercartProductAttributeFilter',
        'ClearSilvercartProductAttributePriceFilter',
    );
    
    /**
     * Initializes the attribute filter before the real controller is initialized
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function onBeforeInit() {
        $request    = $this->owner->getRequest();
        $allParams  = $request->allParams();
        $action     = $allParams['Action'];
        $widget     = $this->getWidget($this->getPreviousSessionKey());
        if ($widget instanceof SilvercartProductAttributeFilterWidget &&
            !$widget->RememberFilter &&
            $this->getSessionKey() != $this->getPreviousSessionKey()) {
            $this->clearFilter($this->getPreviousSessionKey());
            $this->clearFilter($this->getSessionKey());
        }
        $this->setPreviousSessionKey($this->getSessionKey());
        if ($action == 'SilvercartProductAttributeFilter' &&
            $this->owner->getRequest()->isPOST()) {
            $this->initSilvercartProductAttributeFilter($request);
        }
    }
    
    /**
     * Initializes the attribute filter
     *
     * @param SS_HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function initSilvercartProductAttributeFilter(SS_HTTPRequest $request) {
        $widgetID               = $request->postVar('silvercart-product-attribute-widget');
        $widget                 = DataObject::get_by_id('SilvercartProductAttributeFilterWidget', $widgetID);
        $selectedValues         = $request->postVar('silvercart-product-attribute-selected-values');
        $selectedValuesArray    = explode(',', $selectedValues);
        $this->setFilterValues($selectedValuesArray);
        $this->setWidget($widget);
    }

    /**
     * Action to set the attribute filter
     *
     * @param SS_HTTPRequest $request Request
     * 
     * @return string 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function SilvercartProductAttributeFilter(SS_HTTPRequest $request) {
        if (Director::is_ajax()) {
            return $this->owner->renderWith($this->owner->data()->ClassName);
        } else {
            Director::redirectBack();
        }
    }
    
    /**
     * Action to clear the attribute filter
     *
     * @param SS_HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function ClearSilvercartProductAttributeFilter(SS_HTTPRequest $request) {
        $this->clearFilter($this->getSessionKey());
    }
    
    /**
     * Action to clear the attribute filter
     *
     * @param SS_HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function ClearSilvercartProductAttributePriceFilter(SS_HTTPRequest $request) {
        Session::clear('SilvercartProductAttributePriceRangeForm.MinPrice.' . $this->getSessionKey());
        Session::clear('SilvercartProductAttributePriceRangeForm.MaxPrice.' . $this->getSessionKey());
        Director::redirectBack();
    }
    
    /**
     * Returns the unfiltered products of the group
     *
     * @param int    $numberOfProducts Number of products to get
     * @param string $sort             Sort filter
     * @param bool   $disableLimit     Disable product limitation or not?
     * 
     * @return DataObjectSet
     */
    public function getUnfilteredProducts($numberOfProducts = false, $sort = false, $disableLimit = false) {
        $this->disableFilter();
        $products = $this->owner->getProducts($numberOfProducts, $sort, $disableLimit);
        $this->enableFilter();
        return $products;
    }
    
    /**
     * Returns the filter values. If not set they will be set out of session
     * 
     * @return array
     */
    public function getFilterValues() {
        if (is_null($this->filterValues)) {
            $this->setFilterValues(Session::get('SilvercartProductAttributeFilterPlugin.' . $this->getSessionKey()));
        }
        return $this->filterValues;
    }
    
    /**
     * Sets the filter values
     *
     * @param array $filterValues Filter values to set
     * 
     * @return void
     */
    public function setFilterValues($filterValues) {
        $uniqueFilterValues = array_unique((array) $filterValues);
        if (count($uniqueFilterValues) == 1 &&
            array_key_exists(0, $uniqueFilterValues) &&
            empty($uniqueFilterValues[0])) {
            $uniqueFilterValues = array();
        }
        Session::set('SilvercartProductAttributeFilterPlugin.' . $this->getSessionKey(), $uniqueFilterValues);
        Session::save();
        if (empty($uniqueFilterValues)) {
            $this->clearFilter($this->getSessionKey());
        }
        $this->filterValues = $filterValues;
    }
    
    /**
     * Returns the filter widget. If not set it will be set out of session
     * 
     * @param string $sessionKey Optional session key to get widget for
     *
     * @return SilvercartProductAttributeFilterWidget 
     */
    public function getWidget($sessionKey = null) {
        if (is_null($sessionKey)) {
            $sessionKey = $this->getSessionKey();
            if (is_null($this->widget)) {
                $this->setWidget(Session::get('SilvercartProductAttributeFilterWidget.' . $sessionKey));
            }
            $widget = $this->widget;
        } else {
            $widget = Session::get('SilvercartProductAttributeFilterWidget.' . $sessionKey);
        }
        return $widget;
    }
    
    /**
     * Sets the filter widget
     *
     * @param SilvercartProductAttributeFilterWidget $widget Widget
     * 
     * @return void
     */
    public function setWidget($widget) {
        Session::set('SilvercartProductAttributeFilterWidget.' . $this->getSessionKey(), $widget);
        Session::save();
        $this->widget = $widget;
    }
    
    /**
     * Returns whether the given value is part of the current user filter
     *
     * @param SilvercartProductAttributeValue $value Value to check
     * 
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function isFilterValue($value) {
        $isFilterValue  = false;
        $filterValues   = $this->getFilterValues();
        if (is_array($filterValues) &&
            in_array($value->ID, $filterValues)) {
            $isFilterValue = true;
        }
        return $isFilterValue;
    }
    
    /**
     * Returns the filter values as a comma separated list
     *
     * @return string
     */
    public function getFilterValueList() {
        $FilterValueList = '';
        if (is_array($this->getFilterValues())) {
            $FilterValueList = implode(',', $this->getFilterValues());
        }
        return $FilterValueList;
    }
    
    /**
     * Returns the filter values as a DataObjectSet
     *
     * @return DataObjectSet
     */
    public function getFilterValueDataObjectSet() {
        $filterValueDataObjectSet = new DataObjectSet();
        if (is_array($this->getFilterValues())) {
            foreach ($this->getFilterValues() as $filterValue) {
                $filterValueDataObjectSet->push(
                        new DataObject(
                                array(
                                    'ID' => $filterValue,
                                )
                        )
                );
            }
        }
        return $filterValueDataObjectSet;
    }
    
    /**
     * Enables the filter 
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function enableFilter() {
        if (!$this->permanentlyDisableFilter()) {
            $this->filterEnabled = true;
        }
    }
    
    /**
     * Disables the filter 
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function disableFilter() {
        $this->filterEnabled = false;
    }
    
    /**
     * Returns whether the filter is enabled
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function filterEnabled() {
        return $this->filterEnabled;
    }
    
    /**
     * Returns whether the filter is permanently disabled
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.03.2012 
     */
    public function filterPermanentlyDisabled() {
        return $this->filterDisabledPermanently;
    }
    
    /**
     * Disables the filter permanently
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.03.2012 
     */
    public function permanentlyDisableFilter() {
        $this->filterDisabledPermanently = true;
        $this->disableFilter();
    }
    
    /**
     * Clears the attribute filter with the given session key
     *
     * @param string $sessionKey Session key
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.06.2012 
     */
    public function clearFilter($sessionKey) {
        Session::clear('SilvercartProductAttributeFilterPlugin.' . $sessionKey);
        Session::save();
    }
    
    /**
     * Builds and returns the session key dependant on the controller type
     *
     * @return string 
     */
    public function getSessionKey() {
        $sessionKey = $this->owner->ID;
        if ($this->owner instanceof SilvercartSearchResultsPage_Controller) {
            $searchQuery = Convert::raw2sql(Session::get('searchQuery'));
            $sessionKey .= md5($searchQuery) . sha1($searchQuery);
        }
        return $sessionKey;
    }
    
    /**
     * Returns the previous session key
     *
     * @return string
     */
    public function getPreviousSessionKey() {
        $previousSessionKey = Session::get('SilvercartProductAttributeFilterWidget.PreviousSessionKey');
        return $previousSessionKey;
    }
    
    /**
     * Sets the previous session key
     *
     * @param string $previousSessionKey Previous session key
     * 
     * @return void
     */
    public function setPreviousSessionKey($previousSessionKey) {
        Session::set('SilvercartProductAttributeFilterWidget.PreviousSessionKey', $previousSessionKey);
    }
    
    /**
     * Returns the min price
     *
     * @return string
     */
    public function getMinPriceForWidget() {
        return Session::get('SilvercartProductAttributePriceRangeForm.MinPrice.' . $this->getSessionKey());
    }
    
    /**
     * Sets the min price
     *
     * @param string $minPrice Min price
     * 
     * @return void
     */
    public function setMinPriceForWidget($minPrice) {
        Session::set('SilvercartProductAttributePriceRangeForm.MinPrice.' . $this->getSessionKey(), $minPrice);
        Session::save();
    }
    
    /**
     * Returns the max price
     *
     * @return string
     */
    public function getMaxPriceForWidget() {
        return Session::get('SilvercartProductAttributePriceRangeForm.MaxPrice.' . $this->getSessionKey());
    }
    
    /**
     * Sets the max price
     *
     * @param string $maxPrice Max price
     * 
     * @return void
     */
    public function setMaxPriceForWidget($maxPrice) {
        Session::set('SilvercartProductAttributePriceRangeForm.MaxPrice.' . $this->getSessionKey(), $maxPrice);
        Session::save();
    }
    
}