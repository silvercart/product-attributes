<?php

namespace SilverCart\ProductAttributes\Extensions\Order;

use SilverCart\Dev\Tools;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for a order position.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class OrderPositionExtension extends DataExtension {
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'ProductAttributeVariantDefinition' => 'Text',
    ];

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
     * @since 30.05.2018
     */
    public function updateCMSFields(FieldList $fields) {
        if (!$this->cmsFieldsUpdated) {
            if (!$this->owner->exists()) {
                $fields->removeByName('ProductAttributeVariantDefinition');
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
     * @since 30.05.2018
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
            $labels,
            Tools::field_labels_for(static::class),
            [
                'ProductAttributeVariantDefinition' => _t(static::class . '.VARIANTDEFINITION', 'Variant Definition'),
            ]
        );
    }
    
    /**
     * Adds a string to the positions title if related to a variant
     *
     * @param string &$addToTitle String to add
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function addToTitle(&$addToTitle) {
        if (!empty($this->owner->ProductAttributeVariantDefinition)) {
            if (!empty($addToTitle)) {
                $addToTitle .= ' ';
            }
            $addToTitle .= $this->owner->ProductAttributeVariantDefinition;
        }
        return $addToTitle;
    }
}