<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Product\Product;
use SilverCart\Model\Widgets\WidgetController;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeTranslation;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValueTranslation;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\i18n\i18n;

/**
 * Provides grouped filters to display in product group.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductAttributeFilterWidgetController extends WidgetController
{
    /**
     * Possible filter attributes
     *
     * @var ArrayList 
     */
    protected $attributes = null;
    /**
     * Form action for filter form
     *
     * @var string
     */
    protected $formAction = null;
    /**
     * Product list
     *
     * @var ArrayList
     */
    protected $products = null;
    /**
     * ProductGroup Controller
     *
     * @var ProductGroupPageController
     */
    protected $productGroup = null;

    /**
     * Initializes the widget controller
     * 
     * @return void
     */
    public function init() : void
    {
        parent::init();
        if (Controller::curr() instanceof ProductGroupPageController
         && !Controller::curr()->isProductDetailView()
        ) {
            $this->setFormAction(Controller::curr()->data()->Link('ProductAttributeFilter'));
            $attributes = ArrayList::create();
            $products   = $this->getProducts();
            if ($products
             && $products->count() > 0
            ) {
                $productIDs                                = implode(',', $products->map('ID','ID')->toArray());
                $productTableName                          = Product::config()->get('table_name');
                $productAttributeTableName                 = ProductAttribute::config()->get('table_name');
                $productAttributeTranslationTableName      = ProductAttributeTranslation::config()->get('table_name');
                $productAttributeValueTableName            = ProductAttributeValue::config()->get('table_name');
                $productAttributeValueTranslationTableName = ProductAttributeValueTranslation::config()->get('table_name');
                $attributeList = ProductAttribute::get()
                        ->where(
                                sprintf(
                                        '"%s"."ID" IN (%s) AND "%s"."CanBeUsedForFilterWidget" = 1',
                                        $productAttributeTableName,
                                        sprintf(
                                                "SELECT DISTINCT
                                                    %sID
                                                FROM
                                                    %s_ProductAttributes
                                                WHERE
                                                    %sID IN (%s)",
                                                $productAttributeTableName,
                                                $productTableName,
                                                $productTableName,
                                                $productIDs
                                        ),
                                        $productAttributeTableName
                                )
                        )->sort('"' . $productAttributeTranslationTableName . '"."Title" ASC');
                if ($attributeList->count() > 0) {
                    $attributeValues = ProductAttributeValue::get()
                            ->where(
                                    sprintf(
                                            '"%s"."ID" IN (%s)',
                                            $productAttributeValueTableName,
                                            sprintf(
                                                    "SELECT DISTINCT
                                                        %sID
                                                    FROM
                                                        %s_ProductAttributeValues
                                                    WHERE
                                                        %sID IN (%s)",
                                                    $productAttributeValueTableName,
                                                    $productTableName,
                                                    $productTableName,
                                                    $productIDs
                                            )
                                    )
                            )->sort('"' . $productAttributeValueTableName . '"."ProductAttributeID","' . $productAttributeValueTranslationTableName . '"."Title"');
                    if ($attributeValues) {
                        foreach ($attributeList as $attribute) {
                            $values = $attributeValues->filter('ProductAttributeID', $attribute->ID);
                            if ($values->count() > 0) {
                                $attribute->assignValues($values);
                                if ($attribute->hasAssignedValues()) {
                                    $attributes->push($attribute);
                                }
                            }
                        }
                    } else {
                        $attributes = ArrayList::create();
                    }
                }
            }
            if ($attributes instanceof ArrayList) {
                $sortField     = ProductAttributeFilterWidget::config()->product_attribute_sort_field;
                $sortDirection = ProductAttributeFilterWidget::config()->product_attribute_sort_direction;
                if ($sortField === null) {
                    $sortField     = 'Sort';
                    $sortDirection = 'ASC';
                }
                $attributes = $attributes->sort([
                    'HasSelectedValues' => 'DESC',
                    $sortField          => $sortDirection,
                ]);
            } else {
                $attributes = ArrayList::create();
            }
            $this->setAttributes($attributes);
        }
    }

    /**
     * Returns the JS main selector.
     * 
     * @return string
     */
    public function getJsMainSelector() : string
    {
        return ProductAttributeFilterWidget::get_js_main_selector();
    }

    /**
     * Returns the widgets content
     *
     * @return DBHTMLText
     */
    public function Content() : DBHTMLText
    {
        $content = false;
        if (Controller::curr() instanceof ProductGroupPageController
         && !Controller::curr()->isProductDetailView()
         && $this->getAttributes()->Count() > 0
        ) {
            $content = trim(parent::Content());
        }
        return Tools::string2html($content);
    }
    
    /**
     * Returns the attributes
     *
     * @return ArrayList|null
     */
    public function getAttributes() : ?ArrayList
    {
        return $this->attributes;
    }

    /**
     * Sets the Attributes
     *
     * @param ArrayList $attributes Attributes
     * 
     * @return void
     */
    public function setAttributes(ArrayList $attributes) : void
    {
        $this->attributes = $attributes;
    }
    
    /**
     * Returns the form action
     *
     * @return string
     */
    public function getFormAction() : string
    {
        return (string) $this->formAction;
    }
    
    /**
     * Sets the form action
     *
     * @param string $formAction form action
     * 
     * @return void
     */
    public function setFormAction(string $formAction) : void
    {
        $this->formAction = $formAction;
    }

    /**
     * Returns all filter relevant products
     *
     * @return ArrayList
     */
    public function getProducts() : ArrayList
    {
        if (is_null($this->products)) {
            $groupBehaviorController = "SilverCart\\GroupBehavior\\Model\\Pages\\ProductGroupPageController";
            if (class_exists($groupBehaviorController)) {
                $groupBehaviorController::$disable_filter = true;
            }
            if ($this->FilterBehaviour == 'MultipleChoice') {
                $products = ArrayList::create(Controller::curr()->getUnfilteredProducts(false, false, true)->toArray());
                $products->merge(ArrayList::create(Controller::curr()->getInjectedProducts([ProductAttributeFilterWidget::class])->toArray()));
            } else {
                $products = ArrayList::create(Controller::curr()->getProducts(false, false, true, true)->toArray());
                $products->merge(ArrayList::create(Controller::curr()->getInjectedProducts([ProductAttributeFilterWidget::class])->toArray()));
            }
            $this->products = $products;
            if (class_exists($groupBehaviorController)) {
                $groupBehaviorController::$disable_filter = false;
            }
        }
        return $this->products;
    }

    /**
     * Returns the controller of the current product group
     *
     * @return ProductGroupPageController
     */
    public function getProductGroup() : ProductGroupPageController
    {
        if (is_null($this->productGroup)) {
            $productGroup = Controller::curr();
            if (!$productGroup instanceof ProductGroupPageController) {
                $productGroup = ProductGroupPageController::singleton();
            }
            $this->productGroup = $productGroup;
        }
        return $this->productGroup;
    }

    /**
     * Creates the cache key for this widget.
     *
     * @return string
     */
    public function WidgetCacheKey() : string
    {
        $key        = '';
        $products   = $this->getProducts();
        $attributes = $this->getAttributes();
        if ($products->Count() > 0) {
            $productMap           = $products->map('ID', 'LastEditedForCache')->toArray();
            $productMapIDs        = array_keys($productMap);
            sort($productMap);
            sort($productMapIDs);
            $productMapIDString   = implode('-', $productMapIDs);
            $productMapLastEdited = array_pop($productMap);
            $attributesMapIDs     = '';
            $filterValueMapIDs    = $this->getProductGroup()->getFilterValueList();

            if ($attributes->Count() > 0) {
                $attributesMap    = $attributes->map('ID', 'ID')->toArray();
                $attributesMapIDs = implode('-', $attributesMap);
            }
            
            $keyParts = [
                i18n::get_locale(),
                $productMapIDString,
                $productMapLastEdited,
                $this->LastEdited,
                $attributesMapIDs,
                $filterValueMapIDs,
            ];

            $key = implode('_', $keyParts);
        }
        return (string) $key;
    }
}