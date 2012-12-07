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
 * @subpackage Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 06.06.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributePriceRangeForm extends CustomHtmlForm {
    
    /**
     * Set some field values and button labels.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function preferences() {
        $this->preferences['submitButtonTitle']         = _t('SilvercartProductAttributePriceFilterWidget.FILTER');
        $this->preferences['doJsValidationScrolling']   = false;
    }
    
    /**
     * Returns the form fields for this form
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function getFormFields() {
        $minPrice = '';
        $maxPrice = '';
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $minPrice = $this->controller->getMinPriceForWidget();
            $maxPrice = $this->controller->getMaxPriceForWidget();
        }
        $this->formFields = array(
            'MinPrice' => array(
                'type'              => 'TextField',
                'title'             => _t('SilvercartProductAttributePriceFilterWidget.MIN_PRICE'),
                'value'             => $minPrice,
                'checkRequirements' => array(
                    'isFilledIn',
                )
            ),
            'MaxPrice' => array(
                'type'              => 'TextField',
                'title'             => _t('SilvercartProductAttributePriceFilterWidget.MAX_PRICE'),
                'value'             => $maxPrice,
                'checkRequirements' => array(
                    'isFilledIn',
                )
            ),
        );
        return parent::getFormFields();
    }
    
    /**
     * Submit success routine
     *
     * @param SS_HTTPRequest $data     contains the frameworks form data
     * @param Form           $form     not used
     * @param array          $formData contains the modules form data
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function submitSuccess($data, $form, $formData) {
        $this->controller->setMinPriceForWidget($formData['MinPrice']);
        $this->controller->setMaxPriceForWidget($formData['MaxPrice']);
        Director::redirectBack();
    }
    
    /**
     * Returns the cache key extension for this form
     * 
     * @return string
     */
    public function getCacheKeyExtension() {
        return md5($this->controller->getMinPriceForWidget() . $this->controller->getMaxPriceForWidget());
    }
    
    /**
     * Returns the currency
     *
     * @return string
     */
    public function getCurrency() {
        return SilvercartConfig::DefaultCurrency();
    }
    
    /**
     * Returns the currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol() {
        $currency = new Zend_Currency(null, Translatable::get_current_locale());
        return $currency->getSymbol($this->getCurrency(), i18n::get_locale());
    }
    
}