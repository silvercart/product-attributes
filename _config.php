<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage Config
 * @ignore 
 */

// Register FilterPlugins
SilvercartProductGroupPage_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
SilvercartSearchResultsPage_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
SilvercartWidget_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
// register module to use with CustomHtmlForm
CustomHtmlForm::registerModule('silvercart_product_attributes');

if (class_exists('RequirementsEngine')) {
    // ----------------------------------------------------------------------------
    // Register CSS requirements
    // ----------------------------------------------------------------------------
    RequirementsEngine::registerThemedCssFile('SilvercartProductAttribute');
    RequirementsEngine::registerThemedCssFile('SilvercartProductAttributePriceRangeFormField');
    // ----------------------------------------------------------------------------
    // Register JS requirements
    // ----------------------------------------------------------------------------
    RequirementsEngine::registerJsFile(SilvercartTools::getBaseURLSegment() . 'silvercart_product_attributes/js/SilvercartProductAttributeFilterWidget.js');
}