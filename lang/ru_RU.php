<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilvercartPrepaymentPayment.
 *
 * SilvercartPaypalPayment is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilvercartPrepaymentPayment is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilvercartPrepaymentPayment.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Russian language pack
 *
 * @package SilvercartProductAttributes
 * @subpackage i18n
 * @ignore
 */

global $lang;

i18n::include_locale_file('silvercart_product_attributes', 'en_US');

if (array_key_exists('ru_RU', $lang) && is_array($lang['ru_RU'])) {
    $lang['ru_RU'] = array_merge($lang['en_US'], $lang['ru_RU']);
} else {
    $lang['ru_RU'] = $lang['en_US'];
}

$lang['ru_RU']['SilvercartProductAttribute']['PLURALNAME'] = 'Описания товара';
$lang['ru_RU']['SilvercartProductAttribute']['SINGULARNAME'] = 'Описание товара';
$lang['ru_RU']['SilvercartProductAttribute']['TABNAME'] = 'Детали';
$lang['ru_RU']['SilvercartProductAttribute']['TITLE'] = 'Имя';
$lang['ru_RU']['SilvercartProductAttributeSet']['PLURALNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeSet']['SINGULARNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeSet']['TABNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeSet']['TITLE'] = 'Имя';
$lang['ru_RU']['SilvercartProductAttributeValue']['PLURALNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeValue']['SINGULARNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeValue']['TABNAME'] = 'Стоимости';
$lang['ru_RU']['SilvercartProductAttributeValue']['TITLE'] = 'Имя';
$lang['ru_RU']['SilvercartProductAttributeProduct']['PRODUCT_ATTRIBUTES'] = 'Детали';
$lang['ru_RU']['SilvercartProductAttributeProduct']['PRODUCT_ATTRIBUTE_VALUES'] = 'Стоимость';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ACTIVATE_ALL_LABEL'] = 'все активировать';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ASSIGN_LABEL'] = 'Выделить';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ASSIGNEDATTRIBUTES'] = 'выделенные детали';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['DEACTIVATE_ALL_LABEL'] = 'все деактивировать';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['NO_LABEL'] = 'нет';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['NOATTRIBUTESATTRIBUTED'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['NOATTRIBUTESUNATTRIBUTED'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['REMOVE_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['UNASSIGNEDATTRIBUTES'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ACTION_ACTIVATEMATRIX_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ACTION_DEACTIVATEMATRIX_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ACTION_REMOVE_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ACTIONBAR_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['ISACTIVE_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['VALUE_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['YES_LABEL'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['NOVALUESATTRIBUTED'] = '';
$lang['ru_RU']['SilvercartProductAttributeTableListField']['NOVALUESUNATTRIBUTED'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['TITLE'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['CMSTITLE'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['DESCRIPTION'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['DISABLE_FILTER_FOR'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['FILTERBEHAVIOUR'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['FB_HINT'] = '';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['FB_MULTIPLECHOICE'] = 'многократный выбор';
$lang['ru_RU']['SilvercartProductAttributeFilterWidget']['FB_SINGLECHOICE'] = 'простой выбор';
$lang['ru_RU']['SilvercartProductAttributeLanguage']['PLURALNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeLanguage']['SINGULARNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeSetLanguage']['PLURALNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeSetLanguage']['SINGULARNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeValueLanguage']['PLURALNAME'] = '';
$lang['ru_RU']['SilvercartProductAttributeValueLanguage']['SINGULARNAME'] = '';
