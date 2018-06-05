<?php

namespace SilverCart\ProductAttributes\Admin\Forms\GridField;

use SilverCart\Admin\Forms\GridField\GridFieldSubObjectHandler as SilverCartGridFieldSubObjectHandler;

/**
 * Extension for SilverCart\Admin\Forms\GridField\GridFieldSubObjectHandler.
 * Changes the template to use for rendering.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Admin_Forms_GridField
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class GridFieldSubObjectHandler extends SilverCartGridFieldSubObjectHandler {
    
    /**
     * Sets the defaults.
     * 
     * Sets the sublist template.
     * 
     * @param DataObject $parentObject    The parent object.
     * @param string     $targetClassName The target class to execute action for
     * @param DataList   $subList         The sub list to add objects to.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.06.2018
     */
    public function __construct($parentObject, $targetClassName, $subList) {
        parent::__construct($parentObject, $targetClassName, $subList);
        $this->setSubListTemplate(static::class . '_sublist');
    }
    
}
