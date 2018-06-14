<?php

namespace SilverCart\ProductAttributes\Forms\FormFields;

use SilverCart\Forms\FormFields\TextField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\OptionsetField;

/**
 * Form field to choose an engraving.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Forms_FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 13.06.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ChooseEngravingField extends FormField {
    
    use ProductAttributeFormField;
    
    /**
     * Text Field
     *
     * @var TextField
     */
    protected $textField;
    
    /**
     * Optionset Field
     *
     * @var OptionsetField
     */
    protected $optionsetField;
    
    /**
     * Constructor.
     * Calls the parent constructor and creates the text field.
     * 
     * @param string $name        Name
     * @param string $title       Title
     * @param array  $source      Options (map)
     * @param string $value       Selected value
     * @param Form   $form        Form
     * @param string $emptyString Optional string to show for an empty option
     * 
     * @return $this
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.06.2018
     */
    public function __construct($name, $title = null, $source = array(), $value = '', $form = null, $emptyString = null) {
        parent::__construct($name, $title, $value);
        $this->setTextField(TextField::create("{$name}[TextValue]", $title));
        $this->getTextField()->setPlaceholder($title);
        $this->setOptionsetField(OptionsetField::create("{$name}[Option]", $title, $source, $value, $form, $emptyString));
    }
    
    /**
     * Returns the text field.
     * 
     * @return TextField
     */
    public function getTextField() {
        return $this->textField;
    }
    
    /**
     * Sets the text field.
     * 
     * @param TextField $textField Text field
     * 
     * @return $this
     */
    public function setTextField($textField) {
        $this->textField = $textField;
        return $this;
    }
    
    /**
     * Returns the optionset field.
     * 
     * @return OptionsetField
     */
    public function getOptionsetField() {
        return $this->optionsetField;
    }
    
    /**
     * Sets the optionset field.
     * 
     * @param OptionsetField $optionsetField Optionset field
     * 
     * @return $this
     */
    public function setOptionsetField($optionsetField) {
        $this->optionsetField = $optionsetField;
        return $this;
    }
    
    /**
     * Returns whether the optionset field is selected.
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.06.2018
     */
    public function isSelected() {
        return $this->getOptionsetField()->isSelected();
    }

    /**
     * Gets the source array including any empty default values.
     *
     * @return array|ArrayAccess
     */
    public function getSource() {
        return $this->getOptionsetField()->getSource();
    }

    /**
     * Sets the optionset field source.
     * 
     * @param array|ArrayAccess $source Optionset field source
     * 
     * @return $this
     */
    public function setSource($source) {
        $this->getOptionsetField()->setSource($source);

        return $this;
    }

    /**
     * Sets the optionset field has an empty default.
     * 
     * @param boolean $bool Optionset field has empty default?
     * 
     * @return $this
     */
    public function setHasEmptyDefault($bool) {
        $this->getOptionsetField()->setHasEmptyDefault($bool);

        return $this;
    }

    /**
     * Returns whether the optionset field has an empty default.
     * 
     * @return boolean
     */
    public function getHasEmptyDefault() {
        return $this->getOptionsetField()->getHasEmptyDefault();
    }

    /**
     * Set the default selection label, e.g. "select...".
     *
     * Defaults to an empty string. Automatically sets {@link $hasEmptyDefault}
     * to true.
     *
     * @param string $str Optionset field empty string
     * 
     * @return $this
     */
    public function setEmptyString($str) {
        $this->getOptionsetField()->setEmptyString($str);

        return $this;
    }

    /**
     * Returnst the optionset field empty string.
     * 
     * @return string
     */
    public function getEmptyString() {
        return $this->getOptionsetField()->getEmptyString();
    }
    
    /**
     * Returns whether the optionset field has options.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.06.2018
     */
    public function hasOptions() {
        return count($this->getOptionsetField()->getSource()) > 0;
    }

}