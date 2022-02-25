<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Model\Translation\TranslationExtension;
use SilverStripe\ORM\DataObject;

/**
 * Translation for ProductAttributeValue
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 * 
 * @property string $Title Title
 * 
 * @method ProductAttributeValue ProductAttributeValue() Returns the related ProductAttributeValue.
 * 
 * @mixin TranslationExtension
 */
class ProductAttributeValueTranslation extends DataObject
{
    /**
     * Attributes.
     *
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(512)'
    ];
    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    private static $has_one = [
        'ProductAttributeValue' => ProductAttributeValue::class,
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
    private static $table_name = 'SilvercartProductAttributeValueTranslation';
    /**
     * Extensions.
     * 
     * @var string[]
     */
    private static $extensions = [
        TranslationExtension::class,
    ];
    
    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function singular_name() : string
    {
        return (string) Tools::singular_name_for($this);
    }

    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function plural_name() : string
    {
        return (string) Tools::plural_name_for($this);
    }
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     */
    public function fieldLabels($includerelations = true) : array
    {
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            Tools::field_labels_for(static::class),
            Tools::field_labels_for(ProductAttributeValue::class),
            [
                'Title' => _t(ProductAttributeValue::class . '.TITLE', 'Title'),
            ]
        );
        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
}