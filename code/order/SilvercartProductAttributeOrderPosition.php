<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package SilvercartProductAttributes
 * @subpackage Order
 */

/**
 * Fetches additional stylesheets.
 *
 * @package SilvercartProductAttributes
 * @subpackage Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 14.09.2012
 * @license see license file in modules root directory
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributeOrderPosition extends DataExtension {
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = array(
        'ProductAttributeVariantDefinition' => 'Text',
    );

    /**
     * indicator whether the cms fields are already updated or not
     *
     * @var bool
     */
    protected $cmsFieldsUpdated = false;
    
    /**
     * CMS fields
     *
     * @param FieldList $fields Fields to update
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.11.2012
     */
    public function updateCMSFields(FieldList $fields) {
        if (!$this->cmsFieldsUpdated) {
            if ($this->owner->ID > 0) {
                
            }
            $this->cmsFieldsUpdated = true;
        }
    }

    /**
     * Field labels for display in tables.
     *
     * @param array &$labels Field labels to update
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.11.2012
     */
    public function updateFieldLabels(&$labels) {
        parent::updateFieldLabels($labels);
        $labels = array_merge(
            $labels,
            array(
                'ProductAttributeVariantDefinition' => _t('SilvercartProductAttributeOrderPosition.VARIANTDEFINITION'),
            )
        );
    }
}