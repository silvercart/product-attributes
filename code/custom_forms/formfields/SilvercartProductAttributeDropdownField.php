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
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage detail view.
 *
 * @package SilvercartProductAttributes
 * @subpackage FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.09.2012
 * @license see license file in modules root directory
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeDropdownField extends DropdownField {

    /**
     * Returns a <select> tag containing all the appropriate <option> tags.
     * Makes use of {@link FormField->createTag()} to generate the <select>
     * tag and option elements inside is as the content of the <select>.
     * 
     * @return string HTML tag for this dropdown field
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function Field() {
        $options    = '';
        $source     = $this->getSource();
        if ($source) {
            // For SQLMap sources, the empty string needs to be added specially
            if (is_object($source) && $this->emptyString) {
                $options .= $this->createTag('option', array('value' => ''), $this->emptyString);
            }

            foreach ($source as $value => $title) {

                // Blank value of field and source (e.g. "" => "(Any)")
                if ($value === '' && ($this->value === '' || $this->value === null)) {
                    $selected = 'selected';
                } else {
                    // Normal value from the source
                    if ($value) {
                        $selected = ($value == $this->value) ? 'selected' : null;
                    } else {
                        // Do a type check comparison, we might have an array key of 0
                        $selected = ($value === $this->value) ? 'selected' : null;
                    }

                    $this->isSelected = ($selected) ? true : false;
                }

                $options .= $this->createTag(
                        'option', array(
                            'selected'  => $selected,
                            'value'     => $value,
                        ), Convert::raw2xml($title)
                );
            }
        }

        $attributes = array(
            'class'     => ($this->extraClass() ? $this->extraClass() : ''),
            'id'        => $this->id(),
            'name'      => $this->name,
            'tabindex'  => $this->getTabIndex(),
            'rel'       => Controller::curr()->Link() . '/LoadVariant/',
        );

        if ($this->disabled) {
            $attributes['disabled'] = 'disabled';
        }

        return $this->createTag('select', $attributes, $options);
    }

}