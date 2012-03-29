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
 * German (Germany) language pack
 *
 * @package SilvercartProductAttribute
 * @subpackage i18n
 * @ignore
 */

global $lang;

i18n::include_locale_file('silvercart_product_attributes', 'en_US');

if (array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
    $lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
    $lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['SilvercartProductAttribute']['PLURALNAME']                      = 'Artikelmerkmale';
$lang['de_DE']['SilvercartProductAttribute']['SINGULARNAME']                    = 'Artikelmerkmal';

$lang['de_DE']['SilvercartProductAttribute']['TABNAME']                         = 'Merkmale';
$lang['de_DE']['SilvercartProductAttribute']['TITLE']                           = 'Name';

$lang['de_DE']['SilvercartProductAttributeSet']['PLURALNAME']                   = 'Artikelmerkmal-Sets';
$lang['de_DE']['SilvercartProductAttributeSet']['SINGULARNAME']                 = 'Artikelmerkmal-Set';

$lang['de_DE']['SilvercartProductAttributeSet']['TABNAME']                      = 'Merkmal-Sets';
$lang['de_DE']['SilvercartProductAttributeSet']['TITLE']                        = 'Name';

$lang['de_DE']['SilvercartProductAttributeValue']['PLURALNAME']                 = 'Artikelmerkmal-Werte';
$lang['de_DE']['SilvercartProductAttributeValue']['SINGULARNAME']               = 'Artikelmerkmal-Wert';

$lang['de_DE']['SilvercartProductAttributeValue']['TABNAME']                    = 'Werte';
$lang['de_DE']['SilvercartProductAttributeValue']['TITLE']                      = 'Name';

$lang['de_DE']['SilvercartProductAttributeProduct']['PRODUCT_ATTRIBUTES']       = 'Merkmale';
$lang['de_DE']['SilvercartProductAttributeProduct']['PRODUCT_ATTRIBUTE_VALUES'] = 'Wert';

$lang['de_DE']['SilvercartProductAttributeTableListField']['ACTIVATE_ALL_LABEL']            = 'Alle aktivieren';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ASSIGN_LABEL']                  = 'Zuweisen';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ASSIGNEDATTRIBUTES']            = 'Zugewiesene Merkmale';
$lang['de_DE']['SilvercartProductAttributeTableListField']['DEACTIVATE_ALL_LABEL']          = 'Alle deaktivieren';
$lang['de_DE']['SilvercartProductAttributeTableListField']['NO_LABEL']                      = 'nein';
$lang['de_DE']['SilvercartProductAttributeTableListField']['NOATTRIBUTESATTRIBUTED']        = 'Es wurden noch keine Merkmale zugewiesen.';
$lang['de_DE']['SilvercartProductAttributeTableListField']['NOATTRIBUTESUNATTRIBUTED']      = 'Es wurden keine nicht-zugewiesenen Merkmale gefunden.';
$lang['de_DE']['SilvercartProductAttributeTableListField']['REMOVE_LABEL']                  = 'Entfernen';
$lang['de_DE']['SilvercartProductAttributeTableListField']['UNASSIGNEDATTRIBUTES']          = 'Noch nicht zugewiesene Merkmale';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ACTION_ACTIVATEMATRIX_LABEL']   = 'Aktivieren';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ACTION_DEACTIVATEMATRIX_LABEL'] = 'Deaktivieren';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ACTION_REMOVE_LABEL']           = 'Entfernen';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ACTIONBAR_LABEL']               = 'Aktionen';
$lang['de_DE']['SilvercartProductAttributeTableListField']['ISACTIVE_LABEL']                = 'Ist aktiv';
$lang['de_DE']['SilvercartProductAttributeTableListField']['VALUE_LABEL']                   = 'Wert';
$lang['de_DE']['SilvercartProductAttributeTableListField']['YES_LABEL']                     = 'ja';
$lang['de_DE']['SilvercartProductAttributeTableListField']['NOVALUESATTRIBUTED']            = 'Es wurden noch keine Werte zugewiesen.';
$lang['de_DE']['SilvercartProductAttributeTableListField']['NOVALUESUNATTRIBUTED']          = 'Es wurden keine nicht-zugewiesenen Werte gefunden.';

$lang['de_DE']['SilvercartProductAttributeFilterWidget']['TITLE']               = 'Artikelmerkmal-Filter';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['CMSTITLE']            = 'Artikelmerkmal-Filter';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['DESCRIPTION']         = 'Liefert eine gruppierte Auswahl von Filtern, die anhand der zugewiesenen Artikelmerkmale der aktuellen Produktgruppe erstellt wird.';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['DISABLE_FILTER_FOR']  = 'Alle Filter für &quot;%s&quot; aufheben';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['FILTERBEHAVIOUR']     = 'Filter-Verhalten';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['FB_HINT']             = 'Das Feld "Filter-Verhalten" legt fest, wie der Filter reagiert.<br/><b>Mehrfachauswahl:</b> Der Kunde kann mehrere Werte eines Merkmals gleichzeitig auswählen. Zum Beipiel können alle Produkte, deren Farbe rot ODER schwarz ist, angezeigt werden. Es sind immer alle Filter-Merkmal-Werte einer Produktgruppe auswählbar.<br/><b>Einfachauswahl:</b> Der Kunde kann nur einen Wert eines Merkmals auswählen. Die verfügbaren Filter-Merkmal-Werte orientieren sich immer an der gefilterten Produktliste.';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['FB_MULTIPLECHOICE']   = 'Mehrfachauswahl';
$lang['de_DE']['SilvercartProductAttributeFilterWidget']['FB_SINGLECHOICE']     = 'Einfachauswahl';