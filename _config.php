<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or * (at your option) any later version.
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
 * @subpackage Config
 * @ignore 
 */

// Register Extensions
Object::add_extension('SilvercartOrderPosition',                                'SilvercartProductAttributeOrderPosition');
Object::add_extension('SilvercartProduct',                                      'SilvercartProductAttributeProduct');
Object::add_extension('SilvercartProduct_RecordController',                     'SilvercartProductAttributeProduct_RecordController');
Object::add_extension('SilvercartProductPluginProvider',                        'SilvercartProductAttributeProductPlugin');
Object::add_extension('SilvercartProductGroupPage_Controller',                  'SilvercartProductAttributeProductGroupPage_Controller');
// DataObject Translations
Object::add_extension('SilvercartProductAttributeLanguage',                     'SilvercartLanguageDecorator');
Object::add_extension('SilvercartProductAttributeSetLanguage',                  'SilvercartLanguageDecorator');
Object::add_extension('SilvercartProductAttributeValueLanguage',                'SilvercartLanguageDecorator');
// Translatable DataObjects
Object::add_extension('SilvercartProductAttribute',                             'SilvercartDataObjectMultilingualDecorator');
Object::add_extension('SilvercartProductAttributeSet',                          'SilvercartDataObjectMultilingualDecorator');
Object::add_extension('SilvercartProductAttributeValue',                        'SilvercartDataObjectMultilingualDecorator');
// Register SilvercartPlugins
Object::add_extension('SilvercartOrderPluginProvider',                          'SilvercartProductAttributeOrderPlugin');
Object::add_extension('SilvercartOrderPositionPluginProvider',                  'SilvercartProductAttributeOrderPositionPlugin');
Object::add_extension('SilvercartProductAddCartFormDetailPluginProvider',       'SilvercartProductAttributeAddCartFormDetailPlugin');
Object::add_extension('SilvercartShoppingCartPositionPluginProvider',           'SilvercartProductAttributeShoppingCartPositionPlugin');
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