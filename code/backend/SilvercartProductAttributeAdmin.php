<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage ModelAdmins
 */

/**
 * ModelAdmin for SilvercartProductAttributes.
 * 
 * @package Silvercart
 * @subpackage ModelAdmins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2012 pixeltricks GmbH
 * @since 13.03.2012
 * @license see license file in modules root directory
 */
class SilvercartProductAttributeAdmin extends SilvercartModelAdmin {

    /**
     * The code of the menu under which this admin should be shown.
     * 
     * @var string
     */
    public static $menuCode = 'products';

    /**
     * The section of the menu under which this admin should be grouped.
     * 
     * @var string
     */
    public static $menuSortIndex = 100;

    /**
     * The URL segment
     *
     * @var string
     */
    public static $url_segment = 'silvercart-product-attributes';

    /**
     * The menu title
     *
     * @var string
     */
    public static $menu_title = 'Silvercart product attributes';

    /**
     * Managed models
     *
     * @var array
     */
    public static $managed_models = array(
        'SilvercartProductAttribute',
        'SilvercartProductAttributeValue',
        'SilvercartProductAttributeSet',
    );

    /**
     * Definition of the Importers for the managed model.
     *
     * @var array
     */
    public static $model_importers = array(
        'SilvercartProductAttributeSet'     => 'CsvBulkLoader',
        'SilvercartProductAttribute'        => 'CsvBulkLoader',
        'SilvercartProductAttributeValue'   => 'CsvBulkLoader',
    );

    /**
     * Constructor
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function __constructttt() {
        self::$menu_title = _t('SilvercartProductAttribute.PLURALNAME');
        foreach (self::$managed_models as $class => $options) {
            if (is_numeric($class)) {
                unset(self::$managed_models[$class]);
                $class      = $options;
                $options    = array();
            }
            $options['title']               = _t($class . '.TABNAME', singleton($class)->i18n_singular_name());
            self::$managed_models[$class]   = $options;
        }
        parent::__construct();
    }
    
    /**
     * Provides hook for decorators, so that they can overwrite css
     * and other definitions.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2012
     */
    public function initttt() {
        parent::init();
        $this->extend('updateInit');
    }
}



