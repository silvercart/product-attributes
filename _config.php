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

// Register Extensions
SilvercartOrderPosition::add_extension('SilvercartProductAttributeOrderPosition');
SilvercartProduct::add_extension('SilvercartProductAttributeProduct');
SilvercartProductPluginProvider::add_extension('SilvercartProductAttributeProductPlugin');
SilvercartProductGroupPage_Controller::add_extension('SilvercartProductAttributeProductGroupPage_Controller');
// DataObject Translations
SilvercartProductAttributeLanguage::add_extension('SilvercartLanguageDecorator');
SilvercartProductAttributeSetLanguage::add_extension('SilvercartLanguageDecorator');
SilvercartProductAttributeValueLanguage::add_extension('SilvercartLanguageDecorator');
// Translatable DataObjects
SilvercartProductAttribute::add_extension('SilvercartDataObjectMultilingualDecorator');
SilvercartProductAttributeSet::add_extension('SilvercartDataObjectMultilingualDecorator');
SilvercartProductAttributeValue::add_extension('SilvercartDataObjectMultilingualDecorator');
// Register SilvercartPlugins
SilvercartOrderPluginProvider::add_extension('SilvercartProductAttributeOrderPlugin');
SilvercartOrderPositionPluginProvider::add_extension('SilvercartProductAttributeOrderPositionPlugin');
SilvercartProductAddCartFormDetailPluginProvider::add_extension('SilvercartProductAttributeAddCartFormDetailPlugin');
SilvercartShoppingCartPositionPluginProvider::add_extension('SilvercartProductAttributeShoppingCartPositionPlugin');
// Register FilterPlugins
SilvercartProductGroupPage_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
SilvercartSearchResultsPage_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
SilvercartWidget_Controller::registerFilterPlugin('SilvercartProductAttributeProductFilterPlugin');
// register module to use with CustomHtmlForm
CustomHtmlForm::registerModule('silvercart_product_attributes');
// ----------------------------------------------------------------------------
// Register CSS requirements
// ----------------------------------------------------------------------------
RequirementsEngine::registerThemedCssFile('SilvercartProductAttribute');
RequirementsEngine::registerThemedCssFile('SilvercartProductAttributePriceRangeFormField');
// ----------------------------------------------------------------------------
// Register JS requirements
// ----------------------------------------------------------------------------
RequirementsEngine::registerJsFile(SilvercartTools::getBaseURLSegment() . 'silvercart_product_attributes/js/SilvercartProductAttributeFilterWidget.js');