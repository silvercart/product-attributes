<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\SearchResultsPage;
use SilverCart\Model\Pages\SearchResultsPageController;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverCart\ProductAttributes\Model\Widgets\ProductAttributeFilterWidget;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DB;
use SilverStripe\View\ArrayData;

/**
 * Extension for SilverCart ProductGroupPageController.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 * 
 * @property \SilverCart\Model\Pages\ProductGroupPageController $owner Owner
 */
class ProductGroupPageControllerExtension extends GlobalProductAttributesControllerExtension
{
    use \SilverCart\ProductAttributes\Control\PriceRangeController;
    
    const SESSION_KEY_FILTER_PLUGIN = 'SilverCart.ProductAttributeFilterPlugin';
    const SESSION_KEY_FILTER_WIDGET = 'SilverCart.ProductAttributeFilterWidget';
    
    /**
     * Is filter enabled?
     *
     * @var bool
     */
    protected $filterEnabled = true;
    /**
     * Is filter disabled permanently?
     *
     * @var bool
     */
    protected $filterDisabledPermanently = false;
    /**
     * Filter values.
     *
     * @var array 
     */
    protected $filterValues = null;
    /**
     * Context widget
     *
     * @var ProductAttributeFilterWidget
     */
    protected $widget = null;
    /**
     * List of allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'ClearProductAttributeFilter',
        'ClearProductAttributePriceFilter',
        'ProductAttributeFilter',
        'ClearPriceFilter',
        'LoadVariant',
    ];
    
    /**
     * Initializes the attribute filter before the real controller is initialized
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function onBeforeInit()
    {
        $request    = $this->owner->getRequest();
        $allParams  = $request->allParams();
        $action     = $allParams['Action'];
        $widget     = $this->getProductAttributeFilterWidget($this->getPreviousSessionKey());
        if ($widget instanceof ProductAttributeFilterWidget
         && !$widget->RememberFilter
         && $this->getSessionKey() != $this->getPreviousSessionKey()
        ) {
            $this->clearFilter($this->getPreviousSessionKey());
            $this->clearFilter($this->getSessionKey());
        }
        $this->setPreviousSessionKey($this->getSessionKey());
        if ($action == 'ProductAttributeFilter'
         && $this->owner->getRequest()->isPOST()
        ) {
            $this->initProductAttributeFilter($request);
        }
        
        $this->initPriceFilterFromRequest($request);
    }

    /**
     * Initializes the attribute filter
     *
     * @param HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function initProductAttributeFilter(HTTPRequest $request)
    {
        $widgetID            = $request->postVar('silvercart-product-attribute-widget');
        $widget              = ProductAttributeFilterWidget::get()->byID($widgetID);
        $selectedValues      = $request->postVar('silvercart-product-attribute-selected-values');
        $selectedValuesArray = explode(',', $selectedValues);
        $this->setFilterValues($selectedValuesArray);
        $this->setProductAttributeFilterWidget($widget);
    }

    /**
     * Action to set the attribute filter
     *
     * @param HTTPRequest $request Request
     * 
     * @return string 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function ProductAttributeFilter(HTTPRequest $request)
    {
        if (Director::is_ajax()) {
            return $this->owner->renderWith(str_replace('Pages', 'Pages\Layout', $this->owner->data()->ClassName));
        } else {
            $this->owner->redirectBack();
        }
    }
    
    /**
     * Action to clear the attribute filter
     *
     * @param HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function ClearProductAttributeFilter(HTTPRequest $request)
    {
        $this->clearFilter($this->getSessionKey());
    }
    
    /**
     * Action to load the variant with the given parameters
     *
     * @param HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function LoadVariant(HTTPRequest $request)
    {
        $owner      = $this->owner;
        $redirectTo = $owner->Link();
        if ($owner->isProductDetailView()) {
            $product           = $owner->getDetailViewProduct();
            $variantAttributes = $request->postVar('ProductAttributeValue');
            if (is_array($variantAttributes)) {
                foreach ($variantAttributes as $key => $value) {
                    if (empty($value)
                        || !is_numeric($value)
                    ) {
                        unset($variantAttributes[$key]);
                    }
                }
                $variant = $product->getVariantBy($variantAttributes);
                if ($variant) {
                    $redirectTo = $variant->Link();
                }
            }
        }
        $this->owner->redirect(Director::absoluteURL($redirectTo));
    }
    
    /**
     * Returns the unfiltered products of the group
     *
     * @param int    $numberOfProducts Number of products to get
     * @param string $sort             Sort filter
     * @param bool   $disableLimit     Disable product limitation or not?
     * 
     * @return DataList
     */
    public function getUnfilteredProducts($numberOfProducts = false, $sort = false, $disableLimit = false)
    {
        $this->disableFilter();
        $products = $this->owner->getProducts($numberOfProducts, $sort, $disableLimit);
        $this->enableFilter();
        return $products;
    }

    /**
     * Returns the filter values. If not set they will be set out of session
     * 
     * @return array
     */
    public function getFilterValues()
    {
        if (is_null($this->filterValues)) {
            $filterValues = Tools::Session()->get(static::SESSION_KEY_FILTER_PLUGIN . '.' . $this->getSessionKey());
            $this->setFilterValues($filterValues);
        }
        return $this->filterValues;
    }
    
    /**
     * Sets the filter values
     *
     * @param array $filterValues Filter values to set
     * 
     * @return void
     */
    public function setFilterValues($filterValues)
    {
        $uniqueFilterValues = array_unique((array) $filterValues);
        if (count($uniqueFilterValues) == 1
         && array_key_exists(0, $uniqueFilterValues)
         && empty($uniqueFilterValues[0])
        ) {
            $uniqueFilterValues = [];
        } elseif (count($uniqueFilterValues) > 1
               && $uniqueFilterValues[0] === 'NaN'
        ) {
            array_shift($uniqueFilterValues);
        }
        if ($this->owner->getAction() !== 'ProductAttributeFilter'
         && !$this->owner->getRequest()->isPOST()
        ) {
            $chosen = ProductAttribute::getGloballyChosen();
            foreach ($chosen as $attributeID => $attributeValueIDs) {
                if (!is_array($attributeValueIDs)) {
                    continue;
                }
                $uniqueFilterValues = array_merge($uniqueFilterValues, $attributeValueIDs);
            }
            $uniqueFilterValues = array_unique($uniqueFilterValues);
        }
        Tools::Session()->set(static::SESSION_KEY_FILTER_PLUGIN . '.' . $this->getSessionKey(), $uniqueFilterValues);
        Tools::saveSession();
        if (empty($uniqueFilterValues)) {
            $this->clearFilter($this->getSessionKey());
        } else {
            $global = ProductAttribute::getGlobal();
            if ($global !== null
             && $global->exists()
            ) {
                $idList       = implode(',', $uniqueFilterValues);
                $table        = ProductAttributeValue::config()->table_name;
                $result       = DB::query("SELECT DISTINCT ID,ProductAttributeID FROM {$table} WHERE ID IN ({$idList})");
                $idMap        = $result->map();
                $attributeIDs = array_unique($idMap);
                if (in_array($global->ID, $attributeIDs)) {
                    ProductAttribute::setGloballyChosen([]);
                    foreach ($idMap as $attributeValueID => $attributeID) {
                        if ($attributeID !== $global->ID) {
                            continue;
                        }
                        ProductAttributeValue::chooseGloballyByID($attributeID, $attributeValueID, true);
                    }
                } elseif ($this->owner->getAction() !== 'ProductAttributeFilter'
                       && !$this->owner->getRequest()->isPOST()
                ) {
                    $chosen = ProductAttribute::getGloballyChosen();
                    print "<pre>";
                    var_dump($chosen);
                }
            }
        }
        $this->filterValues = $uniqueFilterValues;
    }
    
    /**
     * Returns the filter widget. If not set it will be set out of session
     * 
     * @param string $sessionKey Optional session key to get widget for
     *
     * @return ProductAttributeFilterWidget 
     */
    public function getProductAttributeFilterWidget($sessionKey = null)
    {
        if (is_null($sessionKey)) {
            $sessionKey = $this->getSessionKey();
        }
        $widgetID = Tools::Session()->get(static::SESSION_KEY_FILTER_WIDGET . '.' . $sessionKey);
        $widget   = ProductAttributeFilterWidget::get()->byID($widgetID);
        if (is_null($this->widget)) {
            $this->setProductAttributeFilterWidget($widget);
        }
        return $widget;
    }
    
    /**
     * Sets the filter widget
     *
     * @param ProductAttributeFilterWidget $widget Widget
     * 
     * @return void
     */
    public function setProductAttributeFilterWidget($widget)
    {
        $widgetID = 0;
        if ($widget instanceof ProductAttributeFilterWidget) {
            $widgetID = $widget->ID;
        }
        Tools::Session()->set(static::SESSION_KEY_FILTER_WIDGET . '.' . $this->getSessionKey(), $widgetID);
        Tools::saveSession();
        $this->widget = $widget;
    }
    
    /**
     * Returns whether the given value is part of the current user filter
     *
     * @param ProductAttributeValue $value Value to check
     * 
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function isFilterValue($value)
    {
        $isFilterValue  = false;
        $filterValues   = $this->getFilterValues();
        if (is_array($filterValues)
            && in_array($value->ID, $filterValues)
        ) {
            $isFilterValue = true;
        }
        return $isFilterValue;
    }
    
    /**
     * Returns the filter values as a comma separated list
     *
     * @return string
     */
    public function getFilterValueList()
    {
        $FilterValueList = '';
        if (is_array($this->getFilterValues())) {
            $FilterValueList = implode(',', $this->getFilterValues());
        }
        return $FilterValueList;
    }
    
    /**
     * Returns the filter values as a ArrayList
     *
     * @return ArrayList
     */
    public function getFilterValueArrayList()
    {
        $filterValueArrayList = ArrayList::create();
        if (is_array($this->getFilterValues())) {
            foreach ($this->getFilterValues() as $filterValue) {
                $filterValueArrayList->push(
                        ArrayData::create(['ID' => $filterValue])
                );
            }
        }
        return $filterValueArrayList;
    }
    
    /**
     * Enables the filter 
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function enableFilter()
    {
        if (!$this->permanentlyDisableFilter()) {
            $this->filterEnabled = true;
        }
    }
    
    /**
     * Disables the filter 
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function disableFilter()
    {
        $this->filterEnabled = false;
    }
    
    /**
     * Returns whether the filter is enabled
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.03.2012 
     */
    public function filterEnabled()
    {
        return $this->filterEnabled;
    }
    
    /**
     * Returns whether the filter is permanently disabled
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.03.2012 
     */
    public function filterPermanentlyDisabled()
    {
        return $this->filterDisabledPermanently;
    }
    
    /**
     * Disables the filter permanently
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.03.2012 
     */
    public function permanentlyDisableFilter()
    {
        $this->filterDisabledPermanently = true;
        $this->disableFilter();
    }
    
    /**
     * Clears the attribute filter with the given session key
     *
     * @param string $sessionKey Session key
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function clearFilter($sessionKey)
    {
        Tools::Session()->clear(static::SESSION_KEY_FILTER_PLUGIN . '.' . $sessionKey);
        Tools::saveSession();
    }
    
    /**
     * Builds and returns the session key dependant on the controller type
     *
     * @return string 
     */
    public function getSessionKey()
    {
        $sessionKey = $this->owner->ID;
        if ($this->owner instanceof SearchResultsPageController) {
            $searchQuery = SearchResultsPage::getCurrentSearchQuery();
            $sessionKey .= md5($searchQuery) . sha1($searchQuery);
        }
        return $sessionKey;
    }
    
    /**
     * Returns the previous session key
     *
     * @return string
     */
    public function getPreviousSessionKey()
    {
        $previousSessionKey = Tools::Session()->get(static::SESSION_KEY_FILTER_WIDGET . '.PreviousSessionKey');
        return $previousSessionKey;
    }
    
    /**
     * Sets the previous session key
     *
     * @param string $previousSessionKey Previous session key
     * 
     * @return void
     */
    public function setPreviousSessionKey($previousSessionKey)
    {
        Tools::Session()->set(static::SESSION_KEY_FILTER_WIDGET . '.PreviousSessionKey', $previousSessionKey);
        Tools::saveSession();
    }
}