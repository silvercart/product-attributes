<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
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
 * @license see license file in modules root directory
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
     * @param bool $withUpdate Call the method with decorator updates or not?
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function getFormFields($withUpdate = true) {
        $minPrice = '';
        $maxPrice = '';
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $minPrice = $this->controller->getMinPriceForWidget();
            $maxPrice = $this->controller->getMaxPriceForWidget();
            
            if (is_null($minPrice)) {
                $minPrice = round(Controller::curr()->getMinPriceLimit(), 2);
                $maxPrice = round(Controller::curr()->getMaxPriceLimit(), 2);
            }
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
        return parent::getFormFields($withUpdate);
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
        $this->controller->redirectBack();
    }
    
    /**
     * Returns the cache key extension for this form
     * 
     * @return string
     */
    public function getCacheKeyExtension() {
        return md5($this->controller->ID . '-' . $this->controller->getMinPriceForWidget() . '-' . $this->controller->getMaxPriceForWidget());
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