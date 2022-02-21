<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverStripe\ORM\DataObject;

/**
 * Translation for ProductAttribute.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 */
class ProductAttributeTranslation extends DataObject {
    
    /**
     * Attributes.
     *
     * @var array
     */
    private static $db = [
        'Title'               => 'Varchar(64)',
        'PluralTitle'         => 'Varchar(64)',
        'AdTitle'             => 'Varchar(128)',
        'NavigationItemTitle' => 'Varchar(128)',
        'Description'         => 'Text',
    ];
    
    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    private static $has_one = [
        'ProductAttribute' => ProductAttribute::class,
    ];
    
    /**
     * DB indexes
     * 
     * @var array 
     */
    private static $indexes = [
        'Title' => '("Title")',
    ];
    
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttributeTranslation';

    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function singular_name() {
        return Tools::singular_name_for($this);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string the objects plural name
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function plural_name() {
        return Tools::plural_name_for($this);
    }
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            Tools::field_labels_for(static::class),
            Tools::field_labels_for(ProductAttribute::class),
            [
                'Title'         => _t(ProductAttribute::class . '.TITLE', 'Title'),
                'PluralTitle'   => _t(ProductAttribute::class . '.PLURALTITLE', 'Plural title'),
            ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
}

