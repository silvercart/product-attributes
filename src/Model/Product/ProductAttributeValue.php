<?php

namespace SilverCart\ProductAttributes\Model\Product;

use NumberFormatter;
use SilverCart\Dev\Tools;
use SilverCart\Admin\Forms\AlertField;
use SilverCart\Forms\FormFields\FieldGroup;
use SilverCart\Model\Product\Image as SilverCartImage;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\DataObjectExtension;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
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
 * 
 * @method ProductAttribute ProductAttribute() Returns the related ProductAttribute
 */
class ProductAttributeValue extends DataObject {
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'DefaultModifyTitleAction'         => 'Enum(",add,setTo",null)',
        'DefaultModifyTitleValue'          => 'Varchar(256)',
        'DefaultModifyPriceAction'         => 'Enum(",add,subtract,setTo",null)',
        'DefaultModifyPriceValue'          => 'Varchar(10)',
        'DefaultModifyProductNumberAction' => 'Enum(",add,setTo",null)',
        'DefaultModifyProductNumberValue'  => 'Varchar(50)',
    ];
    
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
        'Title'                          => 'Text',
        'FinalModifyTitleAction'         => 'Text',
        'FinalModifyTitleValue'          => 'Text',
        'FinalModifyPriceAction'         => 'Text',
        'FinalModifyPriceValue'          => 'Text',
        'FinalModifyProductNumberAction' => 'Text',
        'FinalModifyProductNumberValue'  => 'Text',
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
                'Title'                                 => _t(static::class . '.TITLE', 'Title'),
                'ProductAttributeValueTranslations'     => ProductAttributeValueTranslation::singleton()->plural_name(),
                'ProductAttribute'                      => ProductAttribute::singleton()->singular_name(),
                'Products'                              => Product::singleton()->plural_name(),
                'Image'                                 => SilverCartImage::singleton()->singular_name(),
                'Default'                               => _t(static::class . '.Default', "default"),
                'DefaultModifyDesc'                     => _t(static::class . '.DefaultModifyDesc', "Will be used as default for related products. Can be overwritten individually for each product."),
                'DefaultModifyAction'                   => _t(static::class . '.DefaultModifyAction', "Action"),
                'DefaultModifyActionNone'               => _t(static::class . '.DefaultModifyActionNone', "-none-"),
                'DefaultModifyValue'                    => _t(static::class . '.DefaultModifyValue', "Value"),
                'DefaultModifyPrice'                    => _t(static::class . '.DefaultModifyPrice', "Default product price modification"),
                'DefaultModifyPriceAction'              => _t(static::class . '.DefaultModifyAction', "Action"),
                'DefaultModifyPriceActionAdd'           => _t(static::class . '.DefaultModifyActionAdd', "Add"),
                'DefaultModifyPriceActionSetTo'         => _t(static::class . '.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyPriceActionSubtract'      => _t(static::class . '.DefaultModifyActionSubtract', "Subtract"),
                'DefaultModifyPriceValue'               => _t(static::class . '.DefaultModifyValue', "Value"),
                'DefaultModifyProductNumber'            => _t(static::class . '.DefaultModifyProductNumber', "Default product number modification"),
                'DefaultModifyProductNumberAction'      => _t(static::class . '.DefaultModifyAction', "Action"),
                'DefaultModifyProductNumberActionAdd'   => _t(static::class . '.DefaultModifyActionAdd', "Add"),
                'DefaultModifyProductNumberActionSetTo' => _t(static::class . '.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyProductNumberValue'       => _t(static::class . '.DefaultModifyValue', "Value"),
                'DefaultModifyTitle'                    => _t(static::class . '.DefaultModifyTitle', "Default product title modification"),
                'DefaultModifyTitleAction'              => _t(static::class . '.DefaultModifyAction', "Action"),
                'DefaultModifyTitleActionAdd'           => _t(static::class . '.DefaultModifyActionAdd', "Add"),
                'DefaultModifyTitleActionSetTo'         => _t(static::class . '.DefaultModifyActionSetTo', "Set to"),
                'DefaultModifyTitleValue'               => _t(static::class . '.DefaultModifyValue', "Value"),
                'ModifyPrice'                           => _t(static::class . '.ModifyPrice', "Modify product price"),
                'ModifyProductNumber'                   => _t(static::class . '.ModifyProductNumber', "Modify product number"),
                'ModifyTitle'                           => _t(static::class . '.ModifyTitle', "Modify product title"),
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
        if ($this->ProductAttribute()->CanBeUsedForSingleVariants) {
            $fields->dataFieldByName('DefaultModifyPriceValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            $fields->dataFieldByName('DefaultModifyProductNumberValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            $fields->dataFieldByName('DefaultModifyTitleValue')->setRightTitle($this->fieldLabel('DefaultModifyDesc'));
            
            $fields->dataFieldByName('DefaultModifyPriceAction')->setHasEmptyDefault(true);
            $fields->dataFieldByName('DefaultModifyPriceAction')->setEmptyString($this->fieldLabel('DefaultModifyActionNone'));
            $fields->dataFieldByName('DefaultModifyPriceAction')->setSource(Tools::enum_i18n_labels($this, 'DefaultModifyPriceAction', $this->fieldLabel('DefaultModifyActionNone')));
            $fields->dataFieldByName('DefaultModifyProductNumberAction')->setHasEmptyDefault(true);
            $fields->dataFieldByName('DefaultModifyProductNumberAction')->setEmptyString($this->fieldLabel('DefaultModifyActionNone'));
            $fields->dataFieldByName('DefaultModifyProductNumberAction')->setSource(Tools::enum_i18n_labels($this, 'DefaultModifyProductNumberAction', $this->fieldLabel('DefaultModifyActionNone')));
            $fields->dataFieldByName('DefaultModifyTitleAction')->setHasEmptyDefault(true);
            $fields->dataFieldByName('DefaultModifyTitleAction')->setEmptyString($this->fieldLabel('DefaultModifyActionNone'));
            $fields->dataFieldByName('DefaultModifyTitleAction')->setSource(Tools::enum_i18n_labels($this, 'DefaultModifyTitleAction', $this->fieldLabel('DefaultModifyActionNone')));
            
            $priceField = FieldGroup::create('DefaultModifyPrice', $this->fieldLabel('DefaultModifyPrice'), $fields);
            $priceField->push($fields->dataFieldByName('DefaultModifyPriceAction'));
            $priceField->push($fields->dataFieldByName('DefaultModifyPriceValue'));
            $priceField->breakAndPush(AlertField::create('DefaultModifyPriceDesc', $this->fieldLabel('DefaultModifyDesc')));
            
            $productNumberField = FieldGroup::create('DefaultModifyProductNumber', $this->fieldLabel('DefaultModifyProductNumber'), $fields);
            $productNumberField->push($fields->dataFieldByName('DefaultModifyProductNumberAction'));
            $productNumberField->push($fields->dataFieldByName('DefaultModifyProductNumberValue'));
            $productNumberField->breakAndPush(AlertField::create('DefaultModifyProductNumberDesc', $this->fieldLabel('DefaultModifyDesc')));
            
            $titleField = FieldGroup::create('DefaultModifyTitle', $this->fieldLabel('DefaultModifyTitle'), $fields);
            $titleField->push($fields->dataFieldByName('DefaultModifyTitleAction'));
            $titleField->push($fields->dataFieldByName('DefaultModifyTitleValue'));
            $titleField->breakAndPush(AlertField::create('DefaultModifyTitleDesc', $this->fieldLabel('DefaultModifyDesc')));
            
            $fields->addFieldToTab('Root.Main', $priceField);
            $fields->addFieldToTab('Root.Main', $productNumberField);
            $fields->addFieldToTab('Root.Main', $titleField);
        } else {
            $fields->removeByName('DefaultModifyPriceAction');
            $fields->removeByName('DefaultModifyPriceValue');
            $fields->removeByName('DefaultModifyProductNumberAction');
            $fields->removeByName('DefaultModifyProductNumberValue');
            $fields->removeByName('DefaultModifyTitleAction');
            $fields->removeByName('DefaultModifyTitleValue');
        }
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
     * Returns the advertisement title including the attribute.
     * 
     * @param string $valueCssClasses     CSS classes to wrap around the value title
     * @param string $attributeCssClasses CSS classes to wrap around the attribute title
     * 
     * @return DBHTMLText
     */
    public function getFullAdTitle(string $valueCssClasses = '', string $attributeCssClasses = '') : DBHTMLText
    {
        $adValue = $this->getAdTitle();
        $adTitle = $this->ProductAttribute()->AdTitle;
        if (!empty($valueCssClasses)) {
            $adValue = "<span class=\"{$valueCssClasses}\">{$adValue}</span>";
        }
        if (!empty($attributeCssClasses)) {
            $adValue = "<span class=\"{$attributeCssClasses}\">{$adTitle}</span>";
        }
        if (strpos($adTitle, '{$Value}') === false) {
            $adTitle = "{$adValue} {$adTitle}";
        } else {
            $adTitle = str_replace('{$Value}', $adValue, $adTitle);
        }
        return DBHTMLText::create()->setValue((string) $adTitle);
    }
    
    /**
     * Returns the advertisement title including the attribute.
     * Alis for $this->getAdTitle().
     * 
     * @param string $valueCssClasses     CSS classes to wrap around the value title
     * @param string $attributeCssClasses CSS classes to wrap around the attribute title
     * 
     * @return DBHTMLText
     */
    public function FullAdTitle(string $valueCssClasses = '', string $attributeCssClasses = '') : DBHTMLText
    {
        return $this->getFullAdTitle($valueCssClasses, $attributeCssClasses);
    }
    
    /**
     * Returns the advertisement title of this value (without attribute).
     * 
     * @return string
     */
    public function getAdTitle() : string
    {
        $adTitle   = $this->Title;
        $attribute = $this->ProductAttribute();
        if (is_numeric($adTitle)) {
            if ($adTitle === '0'
             && $attribute->DisplayZeroAsUnlimited
            ) {
                $adTitle = $attribute->fieldLabel('unlimited');
            } elseif (!empty ($attribute->DisplayConversionUnit)
                   && $attribute->DisplayConversionFactor > 0
            ) {
                $locale    = i18n::get_locale();
                $formatter = NumberFormatter::create($locale, NumberFormatter::DECIMAL);
                $adTitle   = "{$formatter->format($adTitle / $attribute->DisplayConversionFactor)} {$attribute->DisplayConversionUnit}";
            } else {
                $locale    = i18n::get_locale();
                $formatter = NumberFormatter::create($locale, NumberFormatter::DECIMAL);
                $adTitle   = $formatter->format($adTitle);
            }
        }
        return (string) $adTitle;
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
     * @return bool
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.09.2016
     */
    public function SubObjectHasIsDefault() : bool
    {
        return (bool) $this->ProductAttribute()->CanBeUsedForVariants;
    }
    
    /**
     * Returns the abbreviation for the given action.
     * 
     * @param string $action Action
     * 
     * @return string
     */
    public function getActionAbbreviation($action) {
        $abbr = '';
        switch ($action) {
            case 'add':
                $abbr = '+';
                break;
            case 'setTo':
                $abbr = '=';
                break;
            case 'subtract':
                $abbr = '-';
                break;
            default:
                break;
        }
        return $abbr;
    }
    
    /**
     * Returns the default modification text.
     * 
     * @param string $action Action
     * 
     * @return string
     */
    public function getDefaultModificationText($text, $action) {
        $defaultText = '(' . $this->fieldLabel('Default') . ': ' . $this->getActionAbbreviation($action) . $text . ')';
        return $defaultText;
    }
    
    /**
     * Returns whether the title has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyTitle() {
        return !empty($this->DefaultModifyTitleAction) && !empty($this->DefaultModifyTitleValue);
    }
    
    /**
     * Returns the default title modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyTitleText() {
        $text = '';
        if ($this->DefaultModifyTitle()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyTitleValue, $this->DefaultModifyTitleAction);
        }
        return $text;
    }
    
    /**
     * Returns whether the price has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyPrice() {
        return !empty($this->DefaultModifyPriceAction) && !empty($this->DefaultModifyPriceValue);
    }
    
    /**
     * Returns the default price modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyPriceText() {
        $text = '';
        if ($this->DefaultModifyPrice()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyPriceValue, $this->DefaultModifyPriceAction);
        }
        return $text;
    }
    
    /**
     * Returns whether the product number has a default modification or not.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyProductNumber() {
        return !empty($this->DefaultModifyProductNumberAction) && !empty($this->DefaultModifyProductNumberValue);
    }
    
    /**
     * Returns the default product number modification.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.06.2018
     */
    public function DefaultModifyProductNumberText() {
        $text = '';
        if ($this->DefaultModifyProductNumber()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyProductNumberValue, $this->DefaultModifyProductNumberAction);
        }
        return $text;
    }
    
    /**
     * Returns the final price modification action.
     * 
     * @return string
     */
    public function getFinalModifyPriceAction() {
        if (!empty($this->ModifyPriceAction)) {
            return $this->ModifyPriceAction;
        }
        return $this->DefaultModifyPriceAction;
    }
    
    /**
     * Returns the final price modification value.
     * 
     * @return string
     */
    public function getFinalModifyPriceValue() {
        if (!empty($this->ModifyPriceValue) &&
            !empty($this->ModifyPriceAction)) {
            return $this->ModifyPriceValue;
        }
        return $this->DefaultModifyPriceValue;
    }
    
    /**
     * Returns the final product number modification action.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberAction() {
        if (!empty($this->ModifyProductNumberAction)) {
            return $this->ModifyProductNumberAction;
        }
        return $this->DefaultModifyProductNumberAction;
    }
    
    /**
     * Returns the final product number modification value.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberValue() {
        if (!empty($this->ModifyProductNumberValue) &&
            !empty($this->ModifyProductNumberAction)) {
            return $this->ModifyProductNumberValue;
        }
        return $this->DefaultModifyProductNumberValue;
    }
    
    /**
     * Returns the final title modification action.
     * 
     * @return string
     */
    public function getFinalModifyTitleAction() {
        if (!empty($this->ModifyTitleAction)) {
            return $this->ModifyTitleAction;
        }
        return $this->DefaultModifyTitleAction;
    }
    
    /**
     * Returns the final title modification value.
     * 
     * @return string
     */
    public function getFinalModifyTitleValue() {
        if (!empty($this->ModifyTitleValue) &&
            !empty($this->ModifyTitleAction)) {
            return $this->ModifyTitleValue;
        }
        return $this->DefaultModifyTitleValue;
    }
    
}