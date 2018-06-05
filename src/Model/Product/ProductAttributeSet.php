<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\ORM\DataObjectExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\Map;

/**
 * Attribute set to collect attributes
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 */
class ProductAttributeSet extends DataObject {

    /**
     * has-many relations
     *
     * @var array
     */
    private static $has_many = [
        'ProductAttributeSetTranslations' => ProductAttributeSetTranslation::class,
    ];

    /**
     * many-many relations
     *
     * @var array
     */
    private static $many_many = [
        'ProductAttributes' => ProductAttribute::class,
    ];

    /**
     * Casted attributes
     *
     * @var array
     */
    private static $casting = [
        'Title'                             => 'Text',
        'ProductAttributesAsString'         => 'Text',
        'ProductAttributesForSummaryFields' => DBHTMLText::class,
    ];
    
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttributeSet';

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
            [
                'Title'                           => _t(static::class . '.TITLE', 'Title'),
                'ProductAttributeSetTranslations' => ProductAttributeSetTranslation::singleton()->plural_name(),
                'ProductAttributes'               => ProductAttribute::singleton()->plural_name(),
            ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList the fields for the backend
     */
    public function getCMSFields() {
        $fields = DataObjectExtension::getCMSFields($this);
        return $fields;
    }

    /**
     * Searchable fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function searchableFields() {
        $searchableFields = [
            'ProductAttributeSetTranslations.Title' => [
                'title'  => $this->fieldLabel('Title'),
                'filter' => PartialMatchFilter::class,
            ],
        ];
        $this->extend('updateSearchableFields', $searchableFields);
        return $searchableFields;
    }

    /**
     * Summaryfields for display in tables.
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function summaryFields() {
        $summaryFields = [
            'Title'                     => $this->fieldLabel('Title'),
            'ProductAttributesAsString' => $this->fieldLabel('ProductAttributes'),
        ];
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns the translated title
     *
     * @return string
     */
    public function getTitle() {
        return $this->getTranslationFieldValue('Title');
    }
    
    /**
     * Returns the product attributes as a comma separated string
     *
     * @return string
     */
    public function getProductAttributesAsString() {
        $productAttributesArray = $this->ProductAttributes()->map();
        if ($productAttributesArray instanceof Map) {
            $productAttributesArray = $productAttributesArray->toArray();
        }
        $productAttributesAsString = implode(', ', $productAttributesArray);
        return $productAttributesAsString;
    }
    
    /**
     * Returns the product attributes as a comma separated string
     *
     * @return string
     */
    public function getProductAttributesForSummaryFields() {
        $productAttributesArray = $this->ProductAttributes()->map();
        if ($productAttributesArray instanceof Map) {
            $productAttributesArray = $productAttributesArray->toArray();
        }
        $productAttributesAsString = implode('<br/>' . PHP_EOL, $productAttributesArray);
        return Tools::string2html($productAttributesAsString);
    }
    
}