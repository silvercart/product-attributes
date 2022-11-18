<?php

namespace SilverCart\ProductAttributes\Model\Product;

use NumberFormatter;
use SilverCart\Admin\Forms\AlertField;
use SilverCart\Dev\Tools;
use SilverCart\Extensions\Model\FontAwesomeExtension;
use SilverCart\Forms\FormFields\FieldGroup;
use SilverCart\Model\Product\Product;
use SilverCart\Model\Translation\TranslatableDataObjectExtension;
use SilverCart\Model\URLSegmentable;
use SilverCart\ORM\ExtensibleDataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UnsavedRelationList;
use function _t;

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
 * @property string $DefaultModifyTitleAction         Default Modify Title Action
 * @property string $DefaultModifyTitleValue          Default Modify Title Value
 * @property string $DefaultModifyPriceAction         Default Modify Price Action
 * @property string $DefaultModifyPriceValue          Default Modify Price Value
 * @property string $DefaultModifyProductNumberAction Default Modify ProductNumber Action
 * @property string $DefaultModifyProductNumberValue  Default Modify ProductNumber Value
 * @property string $URLSegment                       URLSegment
 * @property string $Sort                             Sort
 * 
 * @method ProductAttribute ProductAttribute() Returns the related ProductAttribute
 * 
 * @mixin TranslatableDataObjectExtension
 * @mixin FontAwesomeExtension
 */
class ProductAttributeValue extends DataObject
{
    use ExtensibleDataObject;
    use URLSegmentable;
    
    /**
     * Adds this value to or removes this value from the list of globally chosen
     * values.
     * Returns true if the value was added and false if the value was removed.
     * 
     * @param int  $attributeID      Attribute ID
     * @param int  $attributeValueID Attribute value ID
     * @param bool $addOnly          Add only?
     * 
     * @return bool
     */
    public static function chooseGloballyByID(int $attributeID, int $attributeValueID, bool $addOnly = false) : bool
    {
        $added     = false;
        $attribute = ProductAttribute::get()->byID($attributeID);
        if ($attribute->CanBeUsedForFilterWidget
         && $attribute->ShowAsNavigationItem
        ) {
            $chosen = ProductAttribute::getGloballyChosen();
            if (!array_key_exists($attributeID, $chosen)
             || (!$attribute->AllowMultipleChoice
              && !in_array($attributeValueID, $chosen[$attributeID]))
            ) {
                $chosen[$attributeID] = [];
            }
            if (!in_array($attributeValueID, $chosen[$attributeID])) {
                $chosen[$attributeID][] = $attributeValueID;
                $added                  = true;
            } elseif ($addOnly) {
                $added = true;
            } else {
                $key = array_search($attributeValueID, $chosen[$attributeID]);
                unset($chosen[$attributeID][$key]);
            }
            ProductAttribute::setGloballyChosen($chosen);
        }
        return $added;
    }
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'URLSegment'                       => 'Varchar',
        'DisableGlobally'                  => 'Boolean',
        'DefaultModifyTitleAction'         => 'Enum(",add,setTo",null)',
        'DefaultModifyTitleValue'          => 'Varchar(256)',
        'DefaultModifyPriceAction'         => 'Enum(",add,subtract,setTo",null)',
        'DefaultModifyPriceValue'          => 'Varchar(10)',
        'DefaultModifyProductNumberAction' => 'Enum(",add,setTo",null)',
        'DefaultModifyProductNumberValue'  => 'Varchar(50)',
        'Sort'                             => 'Int',
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
        'URLParameter'                   => 'Text',
        'ExampleLink'                    => 'Text',
    ];
    /**
     * default sort
     *
     * @var string
     */
    private static $default_sort = 'Sort, "SilvercartProductAttributeValueTranslation"."Title"';
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttributeValue';
    /**
     * Extensions.
     * 
     * @var string[]
     */
    private static $extensions = [
        TranslatableDataObjectExtension::class,
        FontAwesomeExtension::class,
    ];
    /**
     * Determines to insert the translation CMS fields by TranslatableDataObjectExtension.
     * 
     * @var bool
     */
    private static $insert_translation_cms_fields = true;
    /**
     * Determines to insert the translation CMS fields before this field.
     * 
     * @var string
     */
    private static $insert_translation_cms_fields_before = 'URLSegment';

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
     * @param bool $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     */
    public function fieldLabels($includerelations = true) : array
    {
        return $this->defaultFieldLabels($includerelations, [
            'Title'                                 => _t(static::class . '.TITLE', 'Title'),
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
            'ProductImportList'                     => _t(static::class . '.ProductImportList', 'Import products'),
            'ProductImportListDesc'                 => _t(static::class . '.ProductImportListDesc', 'Add one product number per line into this field to import many product relations at once.'),
        ]);
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList the fields for the backend
     */
    public function getCMSFields() : FieldList
    {
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            if (!$this->ProductAttribute()->isGlobal()) {
                $fields->removeByName('DisableGlobally');
            }
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
            if ($this->ProductAttribute()->ShowAsNavigationItem) {
                $fields->dataFieldByName('URLSegment')->setDescription($this->fieldLabel('URLSegmentDesc'));
                if (!empty($this->URLSegment)) {
                    $fields->insertAfter('URLSegment', ReadonlyField::create('ExampleLink', $this->fieldLabel('ExampleLink'), $this->getExampleLink()));
                    $fields->insertAfter('URLSegment', ReadonlyField::create('URLParameter', $this->fieldLabel('URLParameter'), $this->getURLParameter())->setRightTitle($this->fieldLabel('URLParameterDesc')));
                }
            } else {
                $fields->removeByName('URLSegment');
            }
            if ($this->exists()) {
                $fields->removeByName('Sort');
            }
            $productImportListField = TextareaField::create('ProductImportList', $this->fieldLabel('ProductImportList'));
            $productImportListField->setDescription($this->fieldLabel('ProductImportListDesc'));
            $fields->addFieldToTab('Root.Products', $productImportListField);
        });
        return parent::getCMSFields();
    }

    /**
     * Searchable fields
     *
     * @return array
     */
    public function searchableFields() : array
    {
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
     */
    public function summaryFields() : array
    {
        $summaryFields = [
            'Title'                  => $this->fieldLabel('Title'),
            'ProductAttribute.Title' => $this->fieldLabel('ProductAttribute'),
        ];
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * On before write.
     * 
     * @return void
     */
    protected function onBeforeWrite() : void
    {
        parent::onBeforeWrite();
        if (empty($this->URLSegment)) {
            $this->generateURLSegment(false);
        }
    }
    
    /**
     * Check for import values after writing an attribute in backend.
     * 
     * @return void
     */
    protected function onAfterWrite() : void
    {
        parent::onAfterWrite();
        $request    = Controller::curr()->getRequest();
        $importList = $request->postVar('ProductImportList');
        if (!is_null($importList)
         && !empty($importList)
        ) {
            $productNumbers = explode(PHP_EOL, $importList);
            foreach ($productNumbers as $productNumber) {
                $productNumber = trim($productNumber);
                if (empty($productNumber)) {
                    continue;
                }
                if ($this->Products()->find('ProductNumberShop', $productNumber)) {
                    continue;
                }
                $product = Product::get_by_product_number($productNumber);
                if ($product instanceof Product
                 && $product->exists()
                ) {
                    $this->Products()->add($product);
                }
            }
        }
    }
    
    /**
     * Updates the $records base to validate the URLSegment for.
     * 
     * @param DataList &$records Records to update
     * 
     * @return void
     */
    public function updateGenerateURLSegmentRecords(DataList &$records) : void
    {
        if ($this->ProductAttribute()->exists()) {
            $records = $records->exclude('ProductAttributeID', $this->ProductAttributeID);
        }
    }
    
    /**
     * Returns the URL parameter (HTTP GET) for this attribute value.
     * 
     * @return string
     */
    public function getURLParameter() : string
    {
        return "scpa[{$this->ProductAttribute()->URLSegment}]={$this->URLSegment}";
    }
    
    /**
     * Returns the example link including the URL parameter (HTTP GET) for this 
     * attribute value.
     * 
     * @return string
     */
    public function getExampleLink() : string
    {
        return Director::absoluteURL("?{$this->getURLParameter()}");
    }
    
    /**
     * Returns the related products
     * 
     * @return ManyManyList|UnsavedRelationList
     */
    public function Products() : SS_List
    {
        $products = $this->getManyManyComponents('Products');
        if ($products instanceof ManyManyList) {
            $productAttributeValue = $this;
            $products->addCallbacks()->add(function(ManyManyList $list, Product $item, $extraFields) use ($productAttributeValue) {
                $item->ProductAttributes()->add($productAttributeValue->ProductAttribute());
            }, 'callback-add-product-attribute');
            $products->removeCallbacks()->add(function(ManyManyList $list, array $itemIDs) use ($productAttributeValue) {
                foreach ($itemIDs as $itemID) {
                    $item = Product::get()->byID($itemID);
                    if ($item === null) {
                        continue;
                    }
                    $sistersExists = $item->ProductAttributeValues()
                            ->filter([
                                'ProductAttributeID' => $productAttributeValue->ProductAttributeID,
                            ])
                            ->exclude([
                                'ID' => $productAttributeValue->ID,
                            ])
                            ->exists();
                    if ($sistersExists) {
                        return;
                    }
                    $item->ProductAttributes()->remove($productAttributeValue->ProductAttribute());
                }
            }, 'callback-remove-product-attribute');
        }
        return $products;
    }
    
    /**
     * Returns the translated title
     *
     * @return string
     */
    public function getTitle() : string
    {
        return (string) $this->getTranslationFieldValue('Title');
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
     */
    public function IsFilterValue() : bool
    {
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
     * @return bool
     */
    public function SubObjectHasIsActive() : bool
    {
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
    public function getActionAbbreviation(string $action) : string
    {
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
     * @param string $text   Text
     * @param string $action Action
     * 
     * @return string
     */
    public function getDefaultModificationText(string $text, string $action) : string
    {
        return "({$this->fieldLabel('Default')}: {$this->getActionAbbreviation($action)}{$text})";
    }
    
    /**
     * Returns whether the title has a default modification or not.
     * 
     * @return bool
     */
    public function DefaultModifyTitle() : bool
    {
        return !empty($this->DefaultModifyTitleAction)
            && !empty($this->DefaultModifyTitleValue);
    }
    
    /**
     * Returns the default title modification.
     * 
     * @return string
     */
    public function DefaultModifyTitleText() : string
    {
        $text = '';
        if ($this->DefaultModifyTitle()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyTitleValue, $this->DefaultModifyTitleAction);
        }
        return (string) $text;
    }
    
    /**
     * Returns whether the price has a default modification or not.
     * 
     * @return bool
     */
    public function DefaultModifyPrice() : bool
    {
        return !empty($this->DefaultModifyPriceAction)
            && !empty($this->DefaultModifyPriceValue);
    }
    
    /**
     * Returns the default price modification.
     * 
     * @return string
     */
    public function DefaultModifyPriceText() : string
    {
        $text = '';
        if ($this->DefaultModifyPrice()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyPriceValue, $this->DefaultModifyPriceAction);
        }
        return (string) $text;
    }
    
    /**
     * Returns whether the product number has a default modification or not.
     * 
     * @return bool
     */
    public function DefaultModifyProductNumber() : bool
    {
        return !empty($this->DefaultModifyProductNumberAction)
            && !empty($this->DefaultModifyProductNumberValue);
    }
    
    /**
     * Returns the default product number modification.
     * 
     * @return string
     */
    public function DefaultModifyProductNumberText() : string
    {
        $text = '';
        if ($this->DefaultModifyProductNumber()) {
            $text = $this->getDefaultModificationText($this->DefaultModifyProductNumberValue, $this->DefaultModifyProductNumberAction);
        }
        return (string) $text;
    }
    
    /**
     * Returns the final price modification action.
     * 
     * @return string
     */
    public function getFinalModifyPriceAction() : string
    {
        if (!empty($this->ModifyPriceAction)) {
            return (string) $this->ModifyPriceAction;
        }
        return (string) $this->DefaultModifyPriceAction;
    }
    
    /**
     * Returns the final price modification value.
     * 
     * @return string
     */
    public function getFinalModifyPriceValue() : string
    {
        if (!empty($this->ModifyPriceValue)
         && !empty($this->ModifyPriceAction)
        ) {
            return (string) $this->ModifyPriceValue;
        }
        return (string) $this->DefaultModifyPriceValue;
    }
    
    /**
     * Returns the final product number modification action.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberAction() : string
    {
        if (!empty($this->ModifyProductNumberAction)) {
            return (string) $this->ModifyProductNumberAction;
        }
        return (string) $this->DefaultModifyProductNumberAction;
    }
    
    /**
     * Returns the final product number modification value.
     * 
     * @return string
     */
    public function getFinalModifyProductNumberValue() : string
    {
        if (!empty($this->ModifyProductNumberValue)
         && !empty($this->ModifyProductNumberAction)
        ) {
            return (string) $this->ModifyProductNumberValue;
        }
        return (string) $this->DefaultModifyProductNumberValue;
    }
    
    /**
     * Returns the final title modification action.
     * 
     * @return string
     */
    public function getFinalModifyTitleAction() : string
    {
        if (!empty($this->ModifyTitleAction)) {
            return (string) $this->ModifyTitleAction;
        }
        return (string) $this->DefaultModifyTitleAction;
    }
    
    /**
     * Returns the final title modification value.
     * 
     * @return string
     */
    public function getFinalModifyTitleValue() : string
    {
        if (!empty($this->ModifyTitleValue)
         && !empty($this->ModifyTitleAction)
        ) {
            return (string) $this->ModifyTitleValue;
        }
        return (string) $this->DefaultModifyTitleValue;
    }
    
    /**
     * Adds this value to or removes this value from the list of globally chosen
     * values.
     * Returns true if the value was added and false if the value was removed.
     * 
     * @return bool
     */
    public function chooseGlobally() : bool
    {
        return self::chooseGloballyByID($this->ProductAttribute()->ID, $this->ID);
    }
    
    /**
     * Returns whether this value is globally chosen.
     * 
     * @return bool
     */
    public function IsGloballyChosen() : bool
    {
        $attributeID = $this->ProductAttribute()->ID;
        $chosen      = ProductAttribute::getGloballyChosen();
        return array_key_exists($attributeID, $chosen)
            && in_array($this->ID, $chosen[$attributeID]);
    }
    
    /**
     * Returns the link to choose a value.
     * 
     * @return string|null
     */
    public function ChooseLink() : ?string
    {
        $link = null;
        if ($this->ProductAttribute()->ShowAsNavigationItem
         || $this->ProductAttribute()->RequestInProductGroups
        ) {
            $link = Director::makeRelative("sc-action/choose-product-attribute/{$this->ID}");
        }
        return $link;
    }
}