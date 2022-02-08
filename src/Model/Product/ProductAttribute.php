<?php

namespace SilverCart\ProductAttributes\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Forms\FormFields\TextareaField;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Product\Product;
use SilverCart\ORM\DataObjectExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\SS_List;

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
 * @property string $AdTitle                 Advertisement Title
 * @property string $DisplayConversionUnit   Display Conversion Unit
 * @property float  $DisplayConversionFactor Display Conversion Factor
 * @property bool   $DisplayZeroAsUnlimited  Display Zero As Unlimited
 * @property int    $Sort                    Sort
 */
class ProductAttribute extends DataObject
{
    const SESSION_KEY_UPLOADED_FILE_CONTENT = 'SilverCart.ProductAttributes.UploadedFileContent';
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
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
        'Description'                      => 'Text',
        'ProductAttributeSetsAsString'     => 'Text',
        'ProductAttributeValuesAsString'   => 'Text',
        'HasSelectedValues'                => 'Boolean',
        'CanBeUsedForFilterWidgetString'   => 'Text',
        'CanBeUsedForDataSheetString'      => 'Text',
        'CanBeUsedForVariantsString'       => 'Text',
        'CanBeUsedForSingleVariantsString' => 'Text',
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
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            Tools::field_labels_for(static::class),
            [
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
                'ProductAttributeTranslations'     => ProductAttributeTranslation::singleton()->plural_name(),
                'ProductAttributeValues'           => ProductAttributeValue::singleton()->plural_name(),
                'Products'                         => Product::singleton()->plural_name(),
                'ProductAttributeSets'             => ProductAttributeSet::singleton()->plural_name(),
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
            ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Customized CMS fields
     *
     * @return FieldList
     */
    public function getCMSFields() : FieldList
    {
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->dataFieldByName('CanBeUsedForVariants')->setDescription($this->fieldLabel('CanBeUsedForVariantsDesc'));
            $fields->dataFieldByName('CanBeUsedForSingleVariants')->setDescription($this->fieldLabel('CanBeUsedForSingleVariantsDesc'));
            $fields->dataFieldByName('IsUploadField')->setDescription($this->fieldLabel('IsUploadFieldDesc'));
            $fields->dataFieldByName('AllowedUploadFileEndings')->setDescription($this->fieldLabel('AllowedUploadFileEndingsDesc'));
            $fields->dataFieldByName('AllowedUploadFileMimeTypes')->setDescription($this->fieldLabel('AllowedUploadFileMimeTypesDesc'));
            $fields->dataFieldByName('IsUserInputField')->setDescription($this->fieldLabel('IsUserInputFieldDesc'));
            $fields->dataFieldByName('UserInputFieldMustBeFilledIn')->setDescription($this->fieldLabel('UserInputFieldMustBeFilledInDesc'));
            $fields->dataFieldByName('AdTitle')
                    ->setDescription($this->fieldLabel('AdTitleDesc'))
                    ->setRightTitle($this->fieldLabel('AdTitleRightTitle'));
            $fields->dataFieldByName('Description')->setDescription($this->fieldLabel('DescriptionDesc'));
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
                if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                    $valueGridField->getConfig()->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows('Sort'));
                } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')) {
                    $valueGridField->getConfig()->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows('Sort'));
                }
            }
        });
        return DataObjectExtension::getCMSFields($this, 'CanBeUsedForFilterWidget', false);
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
}