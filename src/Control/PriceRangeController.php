<?php

namespace SilverCart\ProductAttributes\Control;

use SilverCart\Admin\Model\Config;
use SilverCart\Dev\Tools;
use SilverCart\ProductAttributes\Plugins\ProductFilterPlugin;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\Map;

/**
 * Trait for controllers to be able to handle the price range filter.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 07.06.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
trait PriceRangeController
{
    /**
     * Session key for the price range filter.
     *
     * @var string
     */
    private static $session_key_price_range = 'SilverCart.PriceRangeForm';
    /**
     * Min price limit.
     *
     * @var float
     */
    protected $minPriceLimit = null;
    /**
     * Max price limit.
     *
     * @var float
     */
    protected $maxPriceLimit = null;
    
    /**
     * Initializes the min and max price from request data.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function initPriceFilterFromRequest(HTTPRequest $request)
    {
        $minPrice = $request->postVar('MinPrice');
        $maxPrice = $request->postVar('MaxPrice');
        if (is_null($maxPrice)
         && is_null($minPrice)
        ) {
            $minPrice = $request->getVar('MinPrice');
            $maxPrice = $request->getVar('MaxPrice');
        }
        if (!is_numeric($minPrice)
         || !is_numeric($maxPrice)
        ) {
            $minPrice = null;
            $maxPrice = null;
        }
        if (!is_null($maxPrice)
         && !is_null($minPrice)
        ) {
            if ($minPrice > $maxPrice) {
                $tmpPrice = $maxPrice;
                $maxPrice = $minPrice;
                $minPrice = $tmpPrice;
            }
            $this->setMinPriceForWidget($minPrice);
            $this->setMaxPriceForWidget($maxPrice);
        } else {
            Tools::Session()->clear(static::$session_key_price_range . '.MinPrice.' . $this->getSessionKey());
            Tools::Session()->clear(static::$session_key_price_range . '.MaxPrice.' . $this->getSessionKey());
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
    public function ClearProductAttributePriceFilter(HTTPRequest $request)
    {
        Tools::Session()->clear(static::$session_key_price_range . '.MinPrice.' . $this->getSessionKey());
        Tools::Session()->clear(static::$session_key_price_range . '.MaxPrice.' . $this->getSessionKey());
        $this->owner->redirect($this->owner->FilteredLink());
    }
    
    /**
     * Returns the min price
     *
     * @return string
     */
    public function getMinPriceForWidget()
    {
        return Tools::Session()->get(static::$session_key_price_range . '.MinPrice.' . $this->getSessionKey());
    }
    
    /**
     * Sets the min price
     *
     * @param string $minPrice Min price
     * 
     * @return void
     */
    public function setMinPriceForWidget($minPrice)
    {
        if ($minPrice < 0) {
            $minPrice = 0;
        }
        Tools::Session()->set(static::$session_key_price_range . '.MinPrice.' . $this->getSessionKey(), $minPrice);
        Tools::saveSession();
    }
    
    /**
     * Returns the max price
     *
     * @return string
     */
    public function getMaxPriceForWidget()
    {
        return Tools::Session()->get(static::$session_key_price_range . '.MaxPrice.' . $this->getSessionKey());
    }
    
    /**
     * Sets the max price
     *
     * @param string $maxPrice Max price
     * 
     * @return void
     */
    public function setMaxPriceForWidget($maxPrice)
    {
        Tools::Session()->set(static::$session_key_price_range . '.MaxPrice.' . $this->getSessionKey(), $maxPrice);
        Tools::saveSession();
    }

    /**
     * Returns the min price limit
     *
     * @return string
     */
    public function getMinPriceLimit()
    {
        if (is_null($this->minPriceLimit)) {
            ProductFilterPlugin::$skip_filter_once = true;
            $priceType = Config::PriceType();
            $prices    = $this->owner->getProducts(false, false, true, true)->map('ID', 'Price' . ucfirst($priceType) . 'Amount');
            if ($prices instanceof Map) {
                $prices = $prices->toArray();
            }
            sort($prices);
            $this->minPriceLimit = array_shift($prices);
        }
        return $this->minPriceLimit;
    }
    
    /**
     * Returns the max price limit
     *
     * @return string
     */
    public function getMaxPriceLimit()
    {
        if (is_null($this->maxPriceLimit)) {
            ProductFilterPlugin::$skip_filter_once = true;
            $priceType = Config::PriceType();
            $prices    = $this->owner->getProducts(false, false, true, true)->map('ID', 'Price' . ucfirst($priceType) . 'Amount');
            if ($prices instanceof Map) {
                $prices = $prices->toArray();
            }
            rsort($prices);
            $this->maxPriceLimit = array_shift($prices);
        }
        return $this->maxPriceLimit;
    }
}