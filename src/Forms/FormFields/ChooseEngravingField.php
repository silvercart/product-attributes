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
class ChooseEngravingField extends FormField
{
    use ProductAttributeFormField;
    /**
     * Text Field
     *
     * @var TextField|null
     */
    protected $textField = null;
    /**
     * Optionset Field
     *
     * @var OptionsetField|null
     */
    protected $optionsetField = null;
    
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
    public function __construct($name, $title = null, $source = array(), $value = '', $form = null, $emptyString = null)
    {
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
    public function getTextField() : TextField
    {
        if ($this->getRequiredForced()) {
            $this->textField->setRequiredForced(true);
        }
        return $this->textField;
    }
    
    /**
     * Sets the text field.
     * 
     * @param TextField $textField Text field
     * 
     * @return $this
     */
    public function setTextField(TextField $textField) : ChooseEngravingField
    {
        $this->textField = $textField;
        return $this;
    }
    
    /**
     * Returns the optionset field.
     * 
     * @return OptionsetField
     */
    public function getOptionsetField() : OptionsetField
    {
        if ($this->getRequiredForced()) {
            $this->optionsetField->setRequiredForced(true);
        }
        return $this->optionsetField;
    }
    
    /**
     * Sets the optionset field.
     * 
     * @param OptionsetField $optionsetField Optionset field
     * 
     * @return $this
     */
    public function setOptionsetField($optionsetField) : ChooseEngravingField
    {
        $this->optionsetField = $optionsetField;
        return $this;
    }
    
    /**
     * Returns whether the optionset field is selected.
     * 
     * @return bool
     */
    public function isSelected() : bool
    {
        return (bool) $this->getOptionsetField()->isSelected();
    }

    /**
     * Gets the source array including any empty default values.
     *
     * @return array|ArrayAccess
     */
    public function getSource()
    {
        return $this->getOptionsetField()->getSource();
    }

    /**
     * Sets the optionset field source.
     * 
     * @param array|ArrayAccess $source Optionset field source
     * 
     * @return $this
     */
    public function setSource($source) : ChooseEngravingField
    {
        $this->getOptionsetField()->setSource($source);
        return $this;
    }

    /**
     * Sets the optionset field has an empty default.
     * 
     * @param bool $bool Optionset field has empty default?
     * 
     * @return $this
     */
    public function setHasEmptyDefault(bool $bool) : ChooseEngravingField
    {
        $this->getOptionsetField()->setHasEmptyDefault($bool);
        return $this;
    }

    /**
     * Returns whether the optionset field has an empty default.
     * 
     * @return bool
     */
    public function getHasEmptyDefault() : bool
    {
        return (bool) $this->getOptionsetField()->getHasEmptyDefault();
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
    public function setEmptyString(string $str) : ChooseEngravingField
    {
        $this->getOptionsetField()->setEmptyString($str);
        return $this;
    }

    /**
     * Returnst the optionset field empty string.
     * 
     * @return string
     */
    public function getEmptyString() : string
    {
        return (string) $this->getOptionsetField()->getEmptyString();
    }
    
    /**
     * Returns whether the optionset field has options.
     * 
     * @return bool
     */
    public function hasOptions() : bool
    {
        return count($this->getOptionsetField()->getSource()) > 1;
    }
}