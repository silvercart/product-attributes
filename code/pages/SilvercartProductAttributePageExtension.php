<?php
/**
 * Copyright 2016 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage Pages
 */

/**
 * Extension for SilvercartPage_Controller.
 *
 * @package Silvercart
 * @subpackage Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2016 pixeltricks GmbH
 * @since 02.03.2016
 * @license see license file in modules root directory
 */
class SilvercartProductAttributePageExtension_Controller extends DataExtension {
    
    /**
     * Updates the default JS files.
     * 
     * @param array &$jsFiles JS files
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.03.2016
     */
    public function updatedJSRequirements(&$jsFiles) {
        $jsFiles[] = SilvercartTools::get_module_name() . '/js/SilvercartProductAttributeFilterWidget.js';
    }
    
    /**
     * Adds the current locale as JavaScript variable to get the autocompletion
     * i18n context.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.03.2016
     */
    public function onAfterInit() {
        Requirements::themedCss('SilvercartProductAttribute', SilvercartTools::get_module_name());
        Requirements::themedCss('SilvercartProductAttributePriceRangeFormField', SilvercartTools::get_module_name());
        Requirements::customScript('var SCPA_MODULE_NAME = "' . SilvercartTools::get_module_name() . '";', SilvercartTools::get_module_name() . 'ModuleName');
    }
    
}
