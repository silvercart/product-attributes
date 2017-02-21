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
        $productID = $callingObject->getFormFieldDefinition('productID');
        $product   = SilvercartProduct::get()->byID($productID);
        
        if ($product->hasVariants()) {
            $callingObject->fieldGroups['SilvercartProductAttributes'] = $product->getVariantFormFields();
        }
        if ($product->hasSingleProductVariants()) {
            $callingObject->fieldGroups['SilvercartProductAttributesSingle'] = $product->getSingleProductVariantFormFields();
        }
    }
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * @param bool  $force          Set to true to force the call
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function pluginAddCartFormDetailAdditionalFields(&$arguments, &$callingObject, $force = false) {
        if (!$force &&
            get_class($this) != 'SilvercartProductAttributeAddCartFormDetailPlugin') {
            return;
        }
        $productID  = $callingObject->getFormFieldDefinition('productID');
        $product    = SilvercartProduct::get()->byID($productID);
        $output     = '';
        if ($product->hasVariants() ||
            $product->hasSingleProductVariants()) {
            $renderer           = new ViewableData();
            $templateData       = array(
                'Form'              => $callingObject,
                'Controller'        => Controller::curr(),
                'hasVariants'       => $product->hasVariants(),
                'hasSingleProductVariants' => $product->hasSingleProductVariants(),
                'SilvercartProduct' => $product
            );

            $output = $renderer->customise($templateData)->renderWith(
                'SilvercartProductAttributeAddCartFormDetailPlugin'
            );
        }
        return $output;
    }
}

/**
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage list view.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 07.11.2016
 * @license see license file in modules root directory
 * @copyright 2016 pixeltricks GmbH
 */
class SilvercartProductAttributeAddCartFormListPlugin extends SilvercartProductAttributeAddCartFormDetailPlugin {
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginAddCartFormListAdditionalFields(&$arguments, &$callingObject) {
        return $this->pluginAddCartFormDetailAdditionalFields($arguments, $callingObject, true);
    }
    
}

/**
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage list view.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 07.11.2016
 * @license see license file in modules root directory
 * @copyright 2016 pixeltricks GmbH
 */
class SilvercartProductAttributeAddCartFormTilePlugin extends SilvercartProductAttributeAddCartFormDetailPlugin {
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.11.2016
     */
    public function pluginAddCartFormTileAdditionalFields(&$arguments, &$callingObject) {
        return $this->pluginAddCartFormDetailAdditionalFields($arguments, $callingObject, true);
    }
    
}

/**
 * Delivers additional information for the addCartForm CustomHtmlForm object of
 * the SilvercartProductGroupPage list view.
 *
 * @package SilvercartProductAttributes
 * @subpackage Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 21.02.2017
 * @license see license file in modules root directory
 * @copyright 2017 pixeltricks GmbH
 */
class SilvercartProductAttributeAddCartFormPlugin extends SilvercartProductAttributeAddCartFormDetailPlugin {
    
    /**
     * Returns a string of HTML code containing fields to choose a variant.
     *
     * @param array &$arguments     The arguments to pass
     * @param mixed &$callingObject The calling object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.02.2017
     */
    public function pluginAddCartFormAdditionalFields(&$arguments, &$callingObject) {
        return $this->pluginAddCartFormDetailAdditionalFields($arguments, $callingObject, true);
    }
    
}