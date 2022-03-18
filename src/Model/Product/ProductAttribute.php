<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Extensions\Model\FontAwesomeExtension;
use SilverCart\Forms\FormFields\TextareaField;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Customer\Customer;
use SilverCart\Model\Product\Product;
use SilverCart\Model\Translation\TranslatableDataObjectExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\SS_List;
use SilverStripe\Security\Member;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

/**
 * Attribute to relate to a product.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 30.05.2018
 * @license see license file in modules root directory
 * 
 * @property bool   $ShowAsNavigationItem         ShowAsNavigationItem
 * @property bool   $RequestInProductGroups       RequestInProductGroups
 * @property bool   $CanBeUsedForFilterWidget     CanBeUsedForFilterWidget
 * @property bool   $CanBeUsedForDataSheet        CanBeUsedForDataSheet
 * @property bool   $CanBeUsedForVariants         CanBeUsedForVariants
 * @property bool   $CanBeUsedForSingleVariants   CanBeUsedForSingleVariants
 * @property bool   $IsUserInputField             IsUserInputField
 * @property bool   $IsUploadField                IsUploadField
 * @property bool   $UserInputFieldMustBeFilledIn UserInputFieldMustBeFilledIn
 * @property string $AllowedUploadFileEndings     AllowedUploadFileEndings
 * @property string $AllowedUploadFileMimeTypes   AllowedUploadFileMimeTypes
 * @property string $AdTitle                      Advertisement Title
 * @property string $NavigationItemTitle          Navigation Item Title
 * @property string $DisplayConversionUnit        Display Conversion Unit
 * @property float  $DisplayConversionFactor      Display Conversion Factor
 * @property bool   $DisplayZeroAsUnlimited       Display Zero As Unlimited
 * @property string $URLSegment                   URLSegment
 * @property int    $Sort                         Sort
 * 
 * @method \SilverStripe\ORM\HasManyList ProductAttributeTranslations() Returns the related ProductAttributeTranslations.
 * @method \SilverStripe\ORM\HasManyList ProductAttributeValues()       Returns the related ProductAttributeValues.
 * 
 * @method \SilverStripe\ORM\ManyManyList Products()             Returns the related Products.
 * @method \SilverStripe\ORM\ManyManyList ProductAttributeSets() Returns the related ProductAttributeSets.
 * 
 * @mixin TranslatableDataObjectExtension
 * @mixin FontAwesomeExtension
 */
class ProductAttribute extends DataObject
{
    use \SilverCart\ORM\ExtensibleDataObject;
    use \SilverCart\Model\URLSegmentable;
    
    const SESSION_KEY_GLOBALLY_CHOSEN = 'SilverCart.ProductAttributes.GloballyChosen';
    
    /**
     * Goablly available attributes.
     * 
     * @var DataList|null
     */
    protected static $globals = null;
    /**
     * Goablly available attribute.
     * 
     * @var ProductAttribute|null
     */
    protected static $global = null;
    
    /**
     * Applies the global filter attributes on the given $products list.
     * 
     * @param SS_List $products Product list to filter
     * 
     * @return SS_List
     */
    public static function filterProductsGlobally(SS_List $products) : SS_List
    {
        if ($products instanceof DataList) {
            $filterValues = [];
            foreach (self::getGloballyChosen() as $attributeID => $valueIDs) {
                $filterValues = array_merge($filterValues, $valueIDs);
            }
            if (count($filterValues) > 0) {
                $filterValuesString         = "'" . implode("','", $filterValues) . "'";
                $productTable               = Product::config()->table_name;
                $productAttributeValueTable = ProductAttributeValue::config()->table_name;
                $tableAlias                 = 'P_PAV';
                return $products
                        ->leftJoin("{$productTable}_ProductAttributeValues", "{$tableAlias}.{$productTable}ID = {$productTable}.ID", $tableAlias)
                        ->where("{$tableAlias}.{$productAttributeValueTable}ID IN ({$filterValuesString})");
            }
        }
        return $products;
    }
    
    /**
     * Checks whether the global filter matches with the given $product.
     * 
     * @param Product $product Product to check
     * 
     * @return SS_List
     */
    public static function productMatchesGlobally(Product $product) : bool
    {
        $match        = true;
        $filterValues = [];
        foreach (self::getGloballyChosen() as $attributeID => $valueIDs) {
            $filterValues = array_merge($filterValues, $valueIDs);
        }
        if (count($filterValues) > 0) {
            $match = $product->ProductAttributeValues()->filter('ID', $filterValues)->exists();
        }
        return $match;
    }

    /**
     * Returns the gloablly chosen attributes.
     * 
     * @return array
     */
    public static function getGloballyChosen() : array
    {
        $sessionStore   = (array) Tools::Session()->get(self::SESSION_KEY_GLOBALLY_CHOSEN);
        $customerConfig = [];
        $currentUser    = Customer::currentRegisteredCustomer();
        if ($currentUser instanceof Member) {
            $customerConfig = $currentUser->getCustomerConfig()->getProductAttributeSettingsToArray();
            if (!empty($customerConfig)
             && empty($sessionStore)
            ) {
                $sessionStore = $customerConfig;
                Tools::Session()->set(self::SESSION_KEY_GLOBALLY_CHOSEN, $sessionStore);
                Tools::saveSession();
            } elseif (!empty($sessionStore)
                   && empty ($customerConfig)
            ) {
                $currentUser->getCustomerConfig()->writeProductAttributeSettings($sessionStore);
            }
        }
        return $sessionStore;
    }
    
    /**
     * Returns the gloablly available attributes.
     * 
     * @return DataList|null
     */
    public static function getGlobals() : ?DataList
    {
        if (self::$globals === null) {
            self::$globals = self::get()->filter('ShowAsNavigationItem', true);
        }
        return self::$globals;
    }
    
    /**
     * Returns the gloablly available attribute.
     * 
     * @return ProductAttribute|null
     */
    public static function getGlobal() : ?ProductAttribute
    {
        if (self::$global === null) {
            self::$global = self::getGlobals()->first();
        }
        return self::$global;
    }
    
    /**
     * Sets the gloablly chosen attributes.
     * 
     * @param array $chosen Chosen attributes
     * 
     * @return void
     */
    public static function setGloballyChosen(array $chosen) : void
    {
        Tools::Session()->set(self::SESSION_KEY_GLOBALLY_CHOSEN, $chosen);
        Tools::saveSession();
        $currentUser = Customer::currentRegisteredCustomer();
        if ($currentUser instanceof Member) {
            $currentUser->getCustomerConfig()->writeProductAttributeSettings($chosen);
        }
    }
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'AllowMultipleChoice'          => 'Boolean(1)',
        'DisableFilterReset'           => 'Boolean(0)',
        'ShowAsNavigationItem'         => 'Boolean(0)',
        'RequestInProductGroups'       => 'Boolean(0)',
        'CanBeUsedForFilterWidget'     => 'Boolean(1)',
        'CanBeUsedForDataSheet'        => 'Boolean(1)',
        'CanBeUsedForVariants'         => 'Boolean(0)',
        'CanBeUsedForSingleVariants'   => 'Boolean(0)',
        'IsUserInputField'             => 'Boolean(0)',
        'IsUploadField'                => 'Boolean(0)',
        'UserInputFieldMustBeFilledIn' => 'Boolean(0)',
        'AllowedUploadFileEndings'     => 'Text',
        'AllowedUploadFileMimeTypes'   => 'Text',
        'DisplayConversionUnit'        => 'Varchar',
        'DisplayConversionFactor'      => 'Float',
        'DisplayZeroAsUnlimited'       => 'Boolean(0)',
        'URLSegment'                   => 'Varchar',
        'Sort'                         => 'Int',
    ];
    /**
     * has-many relations
     *
     * @var array
     */
    private static $has_many = [
        'ProductAttributeTranslations' => ProductAttributeTranslation::class,
        'ProductAttributeValues'       => ProductAttributeValue::class,
    ];
    /**
     * belongs-many-many relations
     *
     * @var array
     */
    private static $belongs_many_many = [
        'Products'             => Product::class,
        'ProductAttributeSets' => ProductAttributeSet::class,
    ];
    /**
     * Castings
     *
     * @var array
     */
    private static $casting = [
        'Title'                            => 'Text',
        'PluralTitle'                      => 'Text',
        'AdTitle'                          => 'Text',
        'NavigationItemTitle'              => 'Text',
        'Description'                      => 'Text',
        'ProductAttributeSetsAsString'     => 'Text',
        'ProductAttributeValuesAsString'   => 'Text',
        'HasSelectedValues'                => 'Boolean',
        'CanBeUsedForFilterWidgetString'   => 'Text',
        'CanBeUsedForDataSheetString'      => 'Text',
        'CanBeUsedForVariantsString'       => 'Text',
        'CanBeUsedForSingleVariantsString' => 'Text',
        'URLParameter'                     => 'Text',
        'ExampleLink'                      => 'Text',
    ];
    /**
     * DB indexes
     * 
     * @var array 
     */
    private static $indexes = [
        'CanBeUsedForFilterWidget'   => '("CanBeUsedForFilterWidget")',
        'CanBeUsedForDataSheet'      => '("CanBeUsedForDataSheet")',
        'CanBeUsedForVariants'       => '("CanBeUsedForVariants")',
        'CanBeUsedForSingleVariants' => '("CanBeUsedForSingleVariants")',
    ];
    /**
     * Default sort fields and directions
     *
     * @var string
     */
    private static $default_sort = 'Sort, "SilvercartProductAttribute"."CanBeUsedForVariants" DESC, "SilvercartProductAttributeTranslation"."Title"';
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttribute';
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
    private static $insert_translation_cms_fields_before = 'CanBeUsedForFilterWidget';
    /**
     * Assigned values
     *
     * @var ArrayList
     */
    protected $assignedValues = null;
    /**
     * Unassigned values
     *
     * @var ArrayList
     */
    protected $unAssignedValues = null;

    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function singular_name() : string
    {
        return Tools::singular_name_for($this);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function plural_name() : string
    {
        return Tools::plural_name_for($this);
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
        return $this->defaultFieldLabels($includerelations, [
            'AllowedUploadFileEndings'         => _t(static::class . '.AllowedUploadFileEndings', 'Allowed upload file endings'),
            'AllowedUploadFileEndingsDesc'     => _t(static::class . '.AllowedUploadFileEndingsDesc', 'Add one file ending per line (e.g. "jpg", "jpeg", "png").'),
            'AllowedUploadFileMimeTypes'       => _t(static::class . '.AllowedUploadFileMimeTypes', 'Allowed upload file mime types'),
            'AllowedUploadFileMimeTypesDesc'   => _t(static::class . '.AllowedUploadFileMimeTypesDesc', 'Add one file mime type per line (e.g. "image/jpeg", "image/png").'),
            'CanBeUsedForFilterWidget'         => _t(static::class . '.CAN_BE_USED_FOR_FILTERWIDGET', 'Use for product filter'),
            'CanBeUsedForDataSheet'            => _t(static::class . '.CAN_BE_USED_FOR_DATASHEET', 'Use for data sheet'),
            'CanBeUsedForVariants'             => _t(static::class . '.CAN_BE_USED_FOR_VARIANTS', 'Can be used for multi-product-variants'),
            'CanBeUsedForVariantsDesc'         => _t(static::class . '.CAN_BE_USED_FOR_VARIANTS_DESC', 'If this is active, you are able to combine multiple products to use as variants (e.g. one product gets the attribute "color" -> "red", a second one "color" -> "yellow", a third one "color" -> "green").'),
            'CanBeUsedForFilterWidgetShort'    => _t(static::class . '.CanBeUsedForFilterWidgetShort', 'Product filter'),
            'CanBeUsedForDataSheetShort'       => _t(static::class . '.CanBeUsedForDataSheetShort', 'Data sheet'),
            'CanBeUsedForVariantsShort'        => _t(static::class . '.CanBeUsedForVariantsShort', 'Multi-Variants'),
            'CanBeUsedForSingleVariants'       => _t(static::class . '.CanBeUsedForSingleVariants', 'Can be used for single-product-variants'),
            'CanBeUsedForSingleVariantsDesc'   => _t(static::class . '.CanBeUsedForSingleVariantsDesc', 'If this is active, you are able to use a single product with variants (e.g. one product gets the attributes "color" -> "red", "yellow", "green").'),
            'CanBeUsedForSingleVariantsShort'  => _t(static::class . '.CanBeUsedForSingleVariantsShort', 'Single-Variants'),
            'Title'                            => _t(static::class . '.TITLE', 'Title'),
            'PluralTitle'                      => _t(static::class . '.PLURALTITLE', 'Plural title'),
            'ImportList'                       => _t(static::class . '.ImportList', 'Import attributes'),
            'ImportListDesc'                   => _t(static::class . '.ImportListDesc', 'Add one attribute per line into this field to import many attributes at once.'),
            'ImportPrefix'                     => _t(static::class . '.ImportPrefix', 'Import Prefix'),
            'ImportPrefixDesc'                 => _t(static::class . '.ImportPrefixDesc', 'Text will be prefixed to every imported attribute.'),
            'ImportSuffix'                     => _t(static::class . '.ImportSuffix', 'Import Suffix'),
            'ImportSuffixDesc'                 => _t(static::class . '.ImportSuffixDesc', 'Text will be suffixed to every imported attribute.'),
            'IsUploadField'                    => _t(static::class . '.IsUploadField', 'Is upload field'),
            'IsUploadFieldDesc'                => _t(static::class . '.IsUploadFieldDesc', 'If you are using the upload field, the customer can upload a custom file before adding a product to cart.'),
            'IsUserInputField'                 => _t(static::class . '.IsUserInputField', 'Is user input field'),
            'IsUserInputFieldDesc'             => _t(static::class . '.IsUserInputFieldDesc', 'If you are using the user input field, the customer can enter a custom text before adding a product to cart (z.B. "engraving"/"t-shirt overprint").'),
            'unlimited'                        => _t(static::class . '.unlimited', 'unlimited'),
            'UserInputFieldMustBeFilledIn'     => _t(static::class . '.UserInputFieldMustBeFilledIn', 'User input is obligatory'),
            'UserInputFieldMustBeFilledInDesc' => _t(static::class . '.UserInputFieldMustBeFilledInDesc', 'If this is active, the customer has to enter a custom text before he is able to add the related product to cart.'),
            'ProductFilterSettings'            => _t(static::class . '.ProductFilterSettings', 'Filter settings'),
            'SingleVariantSettings'            => _t(static::class . '.SingleVariantSettings', 'Single variant settings'),
        ]);
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList
     */
    public function getCMSFields() : FieldList
    {
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->dataFieldByName('DisableFilterReset')->setDescription($this->fieldLabel('DisableFilterResetDesc'));
            $fields->dataFieldByName('CanBeUsedForVariants')->setDescription($this->fieldLabel('CanBeUsedForVariantsDesc'));
            $fields->dataFieldByName('CanBeUsedForSingleVariants')->setDescription($this->fieldLabel('CanBeUsedForSingleVariantsDesc'));
            $fields->dataFieldByName('IsUploadField')->setDescription($this->fieldLabel('IsUploadFieldDesc'));
            $fields->dataFieldByName('AllowedUploadFileEndings')->setDescription($this->fieldLabel('AllowedUploadFileEndingsDesc'));
            $fields->dataFieldByName('AllowedUploadFileMimeTypes')->setDescription($this->fieldLabel('AllowedUploadFileMimeTypesDesc'));
            $fields->dataFieldByName('IsUserInputField')->setDescription($this->fieldLabel('IsUserInputFieldDesc'));
            $fields->dataFieldByName('UserInputFieldMustBeFilledIn')->setDescription($this->fieldLabel('UserInputFieldMustBeFilledInDesc'));
            $filterToggle = ToggleCompositeField::create(
                    'ProductFilterToggle',
                    $this->fieldLabel('ProductFilterSettings'),
                    [
                        $fields->dataFieldByName('AllowMultipleChoice'),
                        $fields->dataFieldByName('DisableFilterReset'),
                        $fields->dataFieldByName('ShowAsNavigationItem'),
                        $fields->dataFieldByName('RequestInProductGroups'),
                    ]
            )->setHeadingLevel(4)->setStartClosed(true);
            $fields->removeByName('AllowMultipleChoice');
            $fields->removeByName('DisableFilterReset');
            $fields->removeByName('ShowAsNavigationItem');
            $fields->removeByName('RequestInProductGroups');
            $fields->insertAfter($filterToggle, 'CanBeUsedForFilterWidget');
            $singleVariantToggle = ToggleCompositeField::create(
                    'SingleVariantToggle',
                    $this->fieldLabel('SingleVariantSettings'),
                    [
                        $fields->dataFieldByName('IsUserInputField'),
                        $fields->dataFieldByName('IsUploadField'),
                        $fields->dataFieldByName('UserInputFieldMustBeFilledIn'),
                        $fields->dataFieldByName('AllowedUploadFileEndings'),
                        $fields->dataFieldByName('AllowedUploadFileMimeTypes'),
                    ]
            )->setHeadingLevel(4)->setStartClosed(true);
            $fields->removeByName('IsUserInputField');
            $fields->removeByName('IsUploadField');
            $fields->removeByName('UserInputFieldMustBeFilledIn');
            $fields->removeByName('AllowedUploadFileEndings');
            $fields->removeByName('AllowedUploadFileMimeTypes');
            $fields->insertAfter($singleVariantToggle, 'CanBeUsedForSingleVariants');
            if ($this->exists()) {
                $importListField = TextareaField::create('ImportList', $this->fieldLabel('ImportList'));
                $importListField->setDescription($this->fieldLabel('ImportListDesc'));
                $importPrefixField = TextField::create('ImportPrefix', $this->fieldLabel('ImportPrefix'));
                $importPrefixField->setDescription($this->fieldLabel('ImportPrefixDesc'));
                $importSuffixField = TextField::create('ImportSuffix', $this->fieldLabel('ImportSuffix'));
                $importSuffixField->setDescription($this->fieldLabel('ImportSuffixDesc'));

                $fields->addFieldToTab('Root.ProductAttributeValues', $importListField);
                $fields->addFieldToTab('Root.ProductAttributeValues', $importPrefixField);
                $fields->addFieldToTab('Root.ProductAttributeValues', $importSuffixField);
                $valueGridField = $fields->dataFieldByName('ProductAttributeValues');
                if (class_exists(GridFieldOrderableRows::class)) {
                    $valueGridField->getConfig()->addComponent(GridFieldOrderableRows::create('Sort'));
                } elseif (class_exists(GridFieldSortableRows::class)) {
                    $valueGridField->getConfig()->addComponent(new GridFieldSortableRows('Sort'));
                }
            }
            if ($this->ShowAsNavigationItem) {
                $fields->dataFieldByName('URLSegment')->setDescription($this->fieldLabel('URLSegmentDesc'));
                if (!empty($this->URLSegment)
                 && $this->ProductAttributeValues()->exists()
                ) {
                    $fields->insertAfter('URLSegment', ReadonlyField::create('ExampleLink', $this->fieldLabel('ExampleLink'), $this->getExampleLink()));
                    $fields->insertAfter('URLSegment', ReadonlyField::create('URLParameter', $this->fieldLabel('URLParameter'), $this->getURLParameter())->setRightTitle($this->fieldLabel('URLParameterDesc')));
                }
            } else {
                $fields->removeByName('URLSegment');
            }
            if ($this->exists()) {
                $fields->removeByName('Sort');
            }
        });
        $this->afterExtending('updateCMSFields', function(FieldList $fields) {
            $fields->dataFieldByName('AdTitle')
                    ->setDescription($this->fieldLabel('AdTitleDesc'))
                    ->setRightTitle($this->fieldLabel('AdTitleRightTitle'));
            $fields->dataFieldByName('Description')->setDescription($this->fieldLabel('DescriptionDesc'));
            $fields->insertAfter('ShowAsNavigationItem', $fields->dataFieldByName('NavigationItemTitle'));
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
            'ProductAttributeTranslations.Title' => [
                'title'  => $this->fieldLabel('Title'),
                'filter' => PartialMatchFilter::class,
            ],
            'CanBeUsedForFilterWidget' => [
                'title'  => $this->fieldLabel('CanBeUsedForFilterWidget'),
                'filter' => ExactMatchFilter::class,
            ],
            'CanBeUsedForDataSheet' => [
                'title'  => $this->fieldLabel('CanBeUsedForDataSheet'),
                'filter' => ExactMatchFilter::class,
            ],
            'CanBeUsedForVariants' => [
                'title'  => $this->fieldLabel('CanBeUsedForVariants'),
                'filter' => ExactMatchFilter::class,
            ],
            'CanBeUsedForSingleVariants' => [
                'title'  => $this->fieldLabel('CanBeUsedForSingleVariants'),
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
            'Title'                            => $this->fieldLabel('Title'),
            'PluralTitle'                      => $this->fieldLabel('PluralTitle'),
            'CanBeUsedForFilterWidgetString'   => $this->fieldLabel('CanBeUsedForFilterWidgetShort'),
            'CanBeUsedForDataSheetString'      => $this->fieldLabel('CanBeUsedForDataSheetShort'),
            'CanBeUsedForVariantsString'       => $this->fieldLabel('CanBeUsedForVariantsShort'),
            'CanBeUsedForSingleVariantsString' => $this->fieldLabel('CanBeUsedForSingleVariantsShort'),
            'ProductAttributeValuesAsString'   => $this->fieldLabel('ProductAttributeValues'),
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
        $importList = $request->postVar('ImportList');
        if (!is_null($importList) &&
            !empty($importList)) {
            $prefix = $request->postVar('ImportPrefix');
            $suffix = $request->postVar('ImportSuffix');
            if (empty($prefix)) {
                $prefix = '';
            }
            if (empty($suffix)) {
                $suffix = '';
            }
            $attributeValues = explode(PHP_EOL, $importList);
            foreach ($attributeValues as $attributeValueTitle) {
                $attributeValueTitle = trim($attributeValueTitle);
                if ($this->ProductAttributeValues()->find('Title', $attributeValueTitle)) {
                    continue;
                }
                $attributeValue = new ProductAttributeValue();
                $attributeValue->Title = $prefix . $attributeValueTitle . $suffix;
                $attributeValue->write();
                $this->ProductAttributeValues()->add($attributeValue);
            }
        }
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
     * Returns the translated plural title
     *
     * @return string
     */
    public function getPluralTitle() : string
    {
        $pluralTitle = $this->getTranslationFieldValue('PluralTitle');
        if (empty($pluralTitle)) {
            // fall back to title
            $pluralTitle = $this->getTitle();
        }
        return (string) $pluralTitle;
    }
    
    /**
     * Returns the translated ad title
     *
     * @return string
     */
    public function getAdTitle() : string
    {
        $title = $this->getTranslationFieldValue('AdTitle');
        if (empty($title)) {
            // fall back to title
            $title = $this->getTitle();
        }
        return (string) $title;
    }
    
    /**
     * Returns the translated NavigationItemTitle
     *
     * @return string
     */
    public function getNavigationItemTitle() : string
    {
        return (string) $this->getTranslationFieldValue('NavigationItemTitle');
    }
    
    /**
     * Returns the translated Description
     *
     * @return string
     */
    public function getDescription() : string
    {
        $value = $this->getTranslationFieldValue('Description');
        return (string) $value;
    }
    
    /**
     * Returns the URL parameter (HTTP GET) for this attribute value.
     * 
     * @param ProductAttributeValue $value Value to get link for
     * 
     * @return string
     */
    public function getURLParameter(ProductAttributeValue $value = null) : string
    {
        $link = '';
        if ($value === null) {
            $value = $this->ProductAttributeValues()->first();
        }
        if ($value instanceof ProductAttributeValue) {
            $link = "scpa[{$this->URLSegment}]={$value->URLSegment}";
        }
        return $link;
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
     * Returns the AllowedUploadFileEndings list as array.
     * 
     * @return array
     */
    public function getAllowedUploadFileEndingsToArray() : array
    {
        $allowed = (array) explode(PHP_EOL, trim($this->AllowedUploadFileEndings));
        foreach ($allowed as $key => $ending) {
            $allowed[$key] = trim($ending);
        }
        return $allowed;
    }
    
    /**
     * Returns the AllowedUploadFileMimeTypes list as array.
     * 
     * @return array
     */
    public function getAllowedUploadFileMimeTypesToArray() : array
    {
        $allowed = (array) explode(PHP_EOL, trim($this->AllowedUploadFileMimeTypes));
        foreach ($allowed as $key => $type) {
            $allowed[$key] = trim($type);
        }
        return $allowed;
    }
    
    /**
     * Returns the product attribute sets as a comma separated string
     *
     * @return string
     */
    public function getProductAttributeSetsAsString() : string
    {
        $productAttributeSetsArray    = $this->ProductAttributeSets()->map()->toArray();
        $productAttributeSetsAsString = implode(', ', $productAttributeSetsArray);
        return (string) $productAttributeSetsAsString;
    }
    
    /**
     * Returns the product attribute values as a comma separated string
     *
     * @return string
     */
    public function getProductAttributeValuesAsString() : string
    {
        $limit                          = 3;
        $productAttributeValuesAsString = '';
        $addition                       = '';
        if ($this->ProductAttributeValues()->Count() > 0) {
            if ($this->ProductAttributeValues()->Count() > $limit) {
                $productAttributeValuesMap = $this->ProductAttributeValues()->limit($limit)->map();
                $addition                  = ' (und ' . ($this->ProductAttributeValues()->Count() - $limit) . ' weitere)';
            } else {
                $productAttributeValuesMap = $this->ProductAttributeValues()->map();
            }
            $productAttributeValuesAsString = '"' . implode('", "', $productAttributeValuesMap->toArray()) . '"';
            $productAttributeValuesAsString = stripslashes($productAttributeValuesAsString);
        }
        return (string) $productAttributeValuesAsString . $addition;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * filter widget
     *
     * @return string
     */
    public function getCanBeUsedForFilterWidgetString() : string
    {
        $CanBeUsedForFilterWidget = Tools::field_label('No');
        if ($this->CanBeUsedForFilterWidget) {
            $CanBeUsedForFilterWidget = Tools::field_label('Yes');
        }
        return $CanBeUsedForFilterWidget;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * data sheet
     *
     * @return string
     */
    public function getCanBeUsedForDataSheetString() : string
    {
        $CanBeUsedForDataSheet = Tools::field_label('No');
        if ($this->CanBeUsedForDataSheet) {
            $CanBeUsedForDataSheet = Tools::field_label('Yes');
        }
        return $CanBeUsedForDataSheet;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * variants
     *
     * @return string
     */
    public function getCanBeUsedForVariantsString() : string
    {
        $CanBeUsedForVariants = Tools::field_label('No');
        if ($this->CanBeUsedForVariants) {
            $CanBeUsedForVariants = Tools::field_label('Yes');
        }
        return $CanBeUsedForVariants;
    }
    
    /**
     * Returns a string to determine whether the attribute can be used for 
     * variants
     *
     * @return string
     */
    public function getCanBeUsedForSingleVariantsString() : string
    {
        $CanBeUsedForVariants = Tools::field_label('No');
        if ($this->CanBeUsedForSingleVariants) {
            $CanBeUsedForVariants = Tools::field_label('Yes');
        }
        return $CanBeUsedForVariants;
    }
    
    /**
     * Assigns the given values to the assigned values
     *
     * @param ArrayList|DataList $valuesToAssign Values to assign
     * 
     * @return void
     */
    public function assignValues(SS_List $valuesToAssign) : void
    {
        if (is_null($this->assignedValues)) {
            $this->setAssignedValues(ArrayList::create());
        }
        if ($valuesToAssign instanceof DataList) {
            $valuesToAssign = ArrayList::create($valuesToAssign->toArray());
        }
        $this->assignedValues->merge($valuesToAssign);
    }
    
    /**
     * Returns whether this attribute has assigned values in a product or
     * product group context.
     *
     * @return bool
     */
    public function hasAssignedValues() : bool
    {
        $hasAssignedValues = false;
        if (!is_null($this->assignedValues)
         && $this->assignedValues->count() > 0
        ) {
            $hasAssignedValues = true;
        }
        return $hasAssignedValues;
    }

    /**
     * Returns the assigned values in relation to a context product
     *
     * @return ArrayList|null
     */
    public function getAssignedValues() : ?ArrayList
    {
        return $this->assignedValues;
    }

    /**
     * Sets the assigned values in relation to a context product
     *
     * @param ArrayList $assignedValues Assigned values
     * 
     * @return void
     */
    public function setAssignedValues(ArrayList $assignedValues) : void
    {
        $this->assignedValues = $assignedValues;
    }
    
    /**
     * Returns the not assigned values in relation to a context product
     *
     * @return ArrayList
     */
    public function getUnAssignedValues() : ArrayList
    {
        return $this->unAssignedValues;
    }

    /**
     * Sets the not assigned values in relation to a context product
     *
     * @param ArrayList $unAssignedValues Not assigned values
     * 
     * @return void
     */
    public function setUnAssignedValues(ArrayList $unAssignedValues) : void
    {
        $this->unAssignedValues = $unAssignedValues;
    }
    
    /**
     * Returns whether this attribute has a selected value or not
     * 
     * @return bool
     */
    public function getHasSelectedValues() : bool
    {
        $hasSelectedValues = false;
        $assignedValues    = $this->getAssignedValues();
        if (!is_null($assignedValues)) {
            foreach ($assignedValues as $assignedValue) {
                if ($assignedValue->IsFilterValue()) {
                    $hasSelectedValues = true;
                    break;
                }
            }
        }
        return $hasSelectedValues;
    }
    
    /**
     * Returns the uploaded file content for this attribute.
     * 
     * @param array $fileData    Uploaded file data
     * @param int   $attributeID Attribute ID
     * 
     * @return string|null
     */
    public function getUploadedFileContent(array $fileData, int $attributeID = null) : ?string
    {
        $content = null;
        if (file_exists($this->getUploadedFilePath($fileData, $attributeID))) {
            $content = file_get_contents($this->getUploadedFilePath($fileData, $attributeID));
        }
        return $content;
    }
    
    /**
     * Sets the uploaded file content for this attribute.
     * 
     * @param string $content     File content to set
     * @param array  $fileData    Uploaded file data
     * @param int    $attributeID Attribute ID
     * 
     * @return ProductAttribute
     */
    public function setUploadedFileContent(string $content, array $fileData, int $attributeID = null) : ProductAttribute
    {
        file_put_contents($this->getUploadedFilePath($fileData, $attributeID), $content);
        return $this;
    }
    
    /**
     * Returns the file path
     * 
     * @param array $fileData    Uploaded file data
     * @param int   $attributeID Attribute ID
     * 
     * @return string
     */
    public function getUploadedFilePath(array $fileData, int $attributeID = null) : string
    {
        if ($attributeID === null) {
            $attributeID = $this->ID;
        }
        $basePath = TEMP_PATH . '/uploaded-variant-files';
        $fileName = str_replace('/', '_', "{$attributeID}-{$fileData['tmp_name']}-{$fileData['name']}");
        $fullPath = "{$basePath}/{$fileName}";
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }
        return $fullPath;
    }
    
    /**
     * Returns the uploaded file preview.
     * 
     * @param array $fileData    Uploaded file data
     * @param int   $attributeID Attribute ID
     * 
     * @return string|\SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function getUploadedFilePreview(array $fileData, int $attributeID = null)
    {
        $fileName    = $fileData['name'];
        $mimeType    = $fileData['type'];
        $fileContent = $this->getUploadedFileContent($fileData, $attributeID);
        list ($generalType, $detailType) = explode('/', $mimeType);
        if ($generalType === 'image') {
            return $this->renderWith(self::class . '_UploadedImage', [
                'ImageFileName' => $fileName,
                'ImageSource'   => "data:{$mimeType};charset=utf-8;base64," . base64_encode($fileContent),
            ]);
        }
        return $fileName;
    }
    
    /**
     * Returns whether this attribute can be set globally.
     * 
     * @return bool
     */
    public function isGlobal() : bool
    {
        return (bool) $this->ShowAsNavigationItem;
    }
    
    /**
     * Returns whether this attribute has globally chosen values.
     * 
     * @return bool
     */
    public function HasGloballyChosenValues() : bool
    {
        $chosen = self::getGloballyChosen();
        return array_key_exists($this->ID, $chosen)
            && !empty($chosen[$this->ID]);
    }
    
    /**
     * Returns the globally chosen values.
     * 
     * @return SS_List
     */
    public function GloballyChosenValues() : SS_List
    {
        $chosen = self::getGloballyChosen();
        if (array_key_exists($this->ID, $chosen)
         && !empty($chosen[$this->ID])
        ) {
            return $this->ProductAttributeValues()->filterAny('ID', $chosen[$this->ID]);
        }
        return ArrayList::create();
    }
    
    /**
     * Returns the link to reload the global nav item by AJAX.
     * 
     * @return string
     */
    public function ReloadGlobalNavItemLink()
    {
        return Director::makeRelative("sc-action/reload-product-attribute-nav-item");
    }
    
    /**
     * Renders the object with the default template or the template with the given
     * $templateAddition.
     * 
     * @param string $templateAddition Template addition
     * @param string $cssClasses       CSS classes to add
     * 
     * @return DBHTMLText
     */
    public function forTemplate(string $templateAddition = null, string $cssClasses = null) : DBHTMLText
    {
        $template = self::class;
        if ($templateAddition !== null) {
            $template = "{$template}_{$templateAddition}";
        }
        return $this->renderWith($template, [
            'CSSClasses' => $cssClasses,
        ]);
    }
}