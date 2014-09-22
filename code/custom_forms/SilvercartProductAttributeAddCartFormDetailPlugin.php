<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 */

/**
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage detail view.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.09.2012
 * @license see license file in modules root directory
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeAddCartFormDetailPlugin extends DataExtension {
    
    /**
     * We inject our additional fields here.
     * 
     * @param array &$formFields    Form fields to manipulate
     * @param mixed &$callingObject The calling object
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function pluginUpdateFormFields(&$formFields, &$callingObject) {
        $product = Controller::curr()->detailViewProduct;
        if ($product->hasVariants()) {
            $callingObject->fieldGroups['SilvercartProductAttributes'] = $product->getVariantFormFields();
        }
    }
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function pluginAddCartFormDetailAdditionalFields(&$arguments, &$callingObject) {
        $product    = Controller::curr()->detailViewProduct;
        $output     = '';
        if ($product->hasVariants()) {
            $renderer           = new ViewableData();
            $templateData       = array(
                'Form'              => $callingObject,
                'Controller'        => Controller::curr(),
                'hasVariants'       => $product->hasVariants(),
                'SilvercartProduct' => $product
            );

            $output = $renderer->customise($templateData)->renderWith(
                'SilvercartProductAttributeAddCartFormDetailPlugin'
            );
        }
        return $output;
    }
}
