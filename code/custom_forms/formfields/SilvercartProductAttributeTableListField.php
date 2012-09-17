<?php
/**
 * Copyright 2011 pixeltricks GmbH
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
 * @package SilvercartProductAttributes
 * @subpackage FormFields
 */

/**
 * A formfield that displays a list of product attributes and provides buttons
 * to easily add and remove attributes.
 *
 * @package SilvercartProductAttributes
 * @subpackage FormFields
 * @copyright pixeltricks GmbH
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductAttributeTableListField extends FormField {
    
    /**
     * The controller object.
     *
     * @var Object
     */
    protected $controller;
    
    /**
     * Constructor
     *
     * @param Controller $controller The controller object.
     * @param string     $name       The internal field name, passed to forms.
     * @param string     $title      The field label.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function __construct($controller, $name, $title = null) {
        parent::__construct($name, $title);
        $this->controller = $controller;
    }

    /**
     * Returns the rendered HTML code for this field.
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function FieldHolder() {
        Requirements::css(SilvercartTools::getBaseURLSegment() . 'silvercart_product_attributes/css/SilvercartProductAttributeTableListField.css');
        $data       = self::getFieldData($this->controller);
        $pluginData = SilvercartPlugin::call(
            $this,
            'FieldHolder',
            array(
                $data
            ),
            true,
            array()
        );

        if (is_array($pluginData) &&
            $pluginData[0] !== false) {

            $data = $pluginData[0];
        }

        return $this->customise($data)->renderWith($this->class);
    }
    
    /**
     * Returns the data to render the fieldholder with
     *
     * @param DataObject $record            Record to get data for
     * @param int        $activeAttributeID ID of the active attribute
     * 
     * @return array
     */
    public static function getFieldData($record, $activeAttributeID = null) {
        $assignedAttributes     = array();
        $unAssignedAttributes   = array();
        $attributes             = DataObject::get('SilvercartProductAttribute');
        $attributedAttributes   = new DataObjectSet();
        
        // Get assigned and unassigned attribute sets
        if ($attributes) {
            foreach ($attributes as $attribute) {
                if ($record->SilvercartProductAttributes()->find('ID', $attribute->ID)) {
                    $assignedAttributes[] = $attribute;
                } else {
                    $unAssignedAttributes[] = $attribute;
                }
            }
        }
        
        foreach ($record->SilvercartProductAttributes() as $attribute) {
            $assignedValues     = array();
            $unAssignedValues   = array();
            foreach ($attribute->SilvercartProductAttributeValues() as $value) {
                if ($record->SilvercartProductAttributeValues()->find('ID', $value->ID)) {
                    $assignedValues[]   = $value;
                } else {
                    $unAssignedValues[] = $value;
                }
            }
            $attribute->setAssignedValues(new DataObjectSet($assignedValues));
            $attribute->setUnAssignedValues(new DataObjectSet($unAssignedValues));
            $attributedAttributes->push($attribute);
        }
            
        
        $data = array(
            'AttributedAttributes'      => $attributedAttributes,
            'assignedAttributes'        => new DataObjectSet($assignedAttributes),
            'unAssignedAttributes'      => new DataObjectSet($unAssignedAttributes),
            'setActiveAttributeID'      => is_null($activeAttributeID) ? false : true,
            'activeAttributeID'         => $activeAttributeID
        );
        return $data;
    }
    
}