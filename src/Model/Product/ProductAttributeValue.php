<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Model\Product\Image as SilverCartImage;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\DataObjectExtension;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;

/**
 * Value of a product attribute
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 */
class ProductAttributeValue extends DataObject {

    /**
     * has-one relations
     *
     * @var array
     */
    private static $has_one = [
        'ProductAttribute' => ProductAttribute::class,
        'Image'            => Image::class,
    ];

    /**
     * has-many relations
     *
     * @var array
     */
    private static $has_many = [
        'ProductAttributeValueTranslations' => ProductAttributeValueTranslation::class,
    ];

    /**
     * belongs-many-many relations
     *
     * @var array
     */
    private static $belongs_many_many = [
        'Products' => Product::class,
    ];

    /**
     * Casted attributes
     *
     * @var array
     */
    private static $casting = [
        'Title' => 'Text',
    ];

    /**
     * default sort
     *
     * @var string
     */
    private static $default_sort = '"SilvercartProductAttributeValueTranslation"."Title"';
    
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttributeValue';

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
                'Title'                             => _t(static::class . '.TITLE', 'Title'),
                'ProductAttributeValueTranslations' => ProductAttributeValueTranslation::singleton()->plural_name(),
                'ProductAttribute'                  => ProductAttribute::singleton()->singular_name(),
                'Products'                          => Product::singleton()->plural_name(),
                'Image'                             => SilverCartImage::singleton()->singular_name(),
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
            'ProductAttributeValueTranslations.Title' => [
                'title'  => $this->fieldLabel('Title'),
                'filter' => PartialMatchFilter::class,
            ],
            'ProductAttributeID' => [
                'title'  => $this->fieldLabel('ProductAttribute'),
                'filter' => ExactMatchFilter::class,
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
            'Title'                  => $this->fieldLabel('Title'),
            'ProductAttribute.Title' => $this->fieldLabel('ProductAttribute'),
        ];
        
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns the translated title
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function getTitle() {
        return $this->getTranslationFieldValue('Title');
    }
    
    /**
     * Checks wheter the value is used by the current context filter
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.03.2012
     */
    public function IsFilterValue() {
        $isFilterValue = false;
        if (Controller::curr()->hasMethod('isFilterValue')) {
            $isFilterValue = Controller::curr()->isFilterValue($this);
        }
        return $isFilterValue;
    }
    
    /**
     * Returns true to use buttons to toggle IsActive state of a product related
     * attribute value used as variant.
     * 
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function SubObjectHasIsActive() {
        return true;
    }
    
    /**
     * Returns true to use buttons to toggle IsDefault state of a product related
     * attribute value used as variant.
     * 
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function SubObjectHasIsDefault() {
        return true;
    }
    
}