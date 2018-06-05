<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Product\Product;
use SilverCart\Model\Widgets\WidgetController;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeTranslation;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValueTranslation;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
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
class ProductAttributeFilterWidgetController extends WidgetController {
    
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
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function init() {
        parent::init();
        if (Controller::curr() instanceof ProductGroupPageController &&
            !Controller::curr()->isProductDetailView()) {
            $this->setFormAction(Controller::curr()->data()->Link('ProductAttributeFilter'));
            $attributes = new ArrayList();
            $products   = $this->getProducts();
            if ($products &&
                $products->count() > 0) {
                $productIDs = implode(',', $products->map('ID','ID')->toArray());
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
                    $attributeValues    = ProductAttributeValue::get()
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
                            )->sort('"' . $productAttributeTableName . 'ID","' . $productAttributeValueTranslationTableName . '"."Title"');
                    if ($attributeValues) {
                        foreach ($attributeList as $attribute) {
                            $values = $attributeValues->filter('ProductAttributeID', $attribute->ID);
                            if ($values->count() > 0) {
                                $attribute->assignValues($values);
                                if (!(!$attribute->hasAssignedValues() ||
                                      ($attribute->getAssignedValues()->count() == 1 &&
                                      !$attribute->getAssignedValues()->first()->IsFilterValue()))) {
                                    
                                    $attributes->push($attribute);
                                }
                            }
                        }
                    } else {
                        $attributes = new ArrayList();
                    }
                }
            }
            if ($attributes instanceof ArrayList) {
                $attributes = $attributes->sort([
                    'HasSelectedValues' => 'DESC',
                    'Title' => 'ASC',
                ]);
            } else {
                $attributes = new ArrayList();
            }
            $this->setAttributes($attributes);
        }
    }

    /**
     * Returns the JS main selector.
     * 
     * @return void
     */
    public function getJsMainSelector() {
        return ProductAttributeFilterWidget::get_js_main_selector();
    }

    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof ProductGroupPageController &&
            !Controller::curr()->isProductDetailView() &&
            $this->getAttributes()->Count() > 0) {
            $content = trim(parent::Content());
        }
        return $content;
    }
    
    /**
     * Returns the attributes
     *
     * @return ArrayList 
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets the Attributes
     *
     * @param ArrayList $attributes Attributes
     * 
     * @return void
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }
    
    /**
     * Returns the form action
     *
     * @return string
     */
    public function getFormAction() {
        return $this->formAction;
    }
    
    /**
     * Sets the form action
     *
     * @param string $formAction form action
     * 
     * @return void
     */
    public function setFormAction($formAction) {
        $this->formAction = $formAction;
    }

    /**
     * Returns all filter relevant products
     *
     * @return ArrayList
     */
    public function getProducts() {
        if (is_null($this->products)) {
            $groupBehaviorController = "SilverCart\\GroupBehavior\\Model\\Pages\\ProductGroupPageController";
            if (class_exists($groupBehaviorController)) {
                $groupBehaviorController::$disable_filter = true;
            }
            if ($this->FilterBehaviour == 'MultipleChoice') {
                $products = new ArrayList(Controller::curr()->getUnfilteredProducts(false, false, true)->toArray());
                $products->merge(new ArrayList(Controller::curr()->getInjectedProducts([ProductAttributeFilterWidget::class])->toArray()));
            } else {
                $products = new ArrayList(Controller::curr()->getProducts(false, false, true, true)->toArray());
                $products->merge(new ArrayList(Controller::curr()->getInjectedProducts([ProductAttributeFilterWidget::class])->toArray()));
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
    public function getProductGroup() {
        if (is_null($this->productGroup)) {
            $productGroup = Controller::curr();
            if (!$productGroup instanceof ProductGroupPageController) {
                $productGroup = new ProductGroupPageController();
            }
            $this->productGroup = $productGroup;
        }
        return $this->productGroup;
    }

    /**
     * Creates the cache key for this widget.
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function WidgetCacheKey() {
        $key        = '';
        $products   = $this->getProducts();
        $attributes = $this->getAttributes();
        
        if ($products->Count() > 0) {
            $productMap           = $products->map('ID', 'LastEditedForCache')->toArray();
            $productMapIDs        = implode('-', array_keys($productMap));
            sort($productMap);
            $productMapLastEdited = array_pop($productMap);
            $attributesMapIDs     = '';
            $filterValueMapIDs    = $this->getProductGroup()->getFilterValueList();

            if ($attributes->Count() > 0) {
                $attributesMap    = $attributes->map('ID', 'ID');
                $attributesMapIDs = implode('-', $attributesMap);
            }
            
            $keyParts = [
                i18n::get_locale(),
                $productMapIDs,
                $productMapLastEdited,
                $this->LastEdited,
                $attributesMapIDs,
                $filterValueMapIDs,
            ];

            $key = implode('_', $keyParts);
        }
        return $key;
    }
}