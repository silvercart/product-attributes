<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
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
 * @license see license file in modules root directory
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
     * @param array $properties key value pairs of template variables
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
    */
   public function FieldHolder($properties = array()) {
        Requirements::css(SilvercartTools::get_module_name() . '/css/SilvercartProductAttributeTableListField.css');
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
        $attributes             = SilvercartProductAttribute::get();
        $attributedAttributes   = new ArrayList();
        
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
            $attribute->setAssignedValues(new ArrayList($assignedValues));
            $attribute->setUnAssignedValues(new ArrayList($unAssignedValues));
            $attributedAttributes->push($attribute);
        }
            
        
        $data = array(
            'AttributedAttributes'      => $attributedAttributes,
            'assignedAttributes'        => new ArrayList($assignedAttributes),
            'unAssignedAttributes'      => new ArrayList($unAssignedAttributes),
            'setActiveAttributeID'      => is_null($activeAttributeID) ? false : true,
            'activeAttributeID'         => $activeAttributeID
        );
        return $data;
    }
    
}