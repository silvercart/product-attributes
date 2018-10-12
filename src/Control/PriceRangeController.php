<?php

namespace SilverCart\ProductAttributes\Control;

use SilverCart\Admin\Model\Config;
use SilverCart\Dev\Tools;
use SilverCart\ORM\FieldType\DBMoney;
use SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget;
use SilverCart\ProductAttributes\Plugins\ProductFilterPlugin;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\Map;
use SilverStripe\View\ArrayData;

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
     * Maximum value to make price range suggestions for.
     *
     * @var double
     */
    private static $max_suggestion_limit = 500;
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
            $this->minPriceLimit = round(array_shift($prices), 2);
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
            $this->maxPriceLimit = round(array_shift($prices), 2);
        }
        return $this->maxPriceLimit;
    }
    
    /**
     * Returns the price range suggestions for the current product group context.
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function getPriceRangeSuggestions()
    {
        $upperEnd    = $this->owner->config()->get('max_suggestion_limit');
        $suggestions = ArrayList::create();
        $currency    = Config::DefaultCurrency();
        $minLimit    = $this->getMinPriceLimit();
        $maxLimit    = $this->getMaxPriceLimit();
        $ranges      = [
            2.5 => [0.2,0.5,1],
            5   => [1,2.5,5],
            10  => [2.5,5,10],
            20  => [5,10,15,20],
            50  => [5,10,20],
            100 => [5,10,20,50],
            200 => [5,10,25,50,100,150],
            500 => [5,10,25,50,100,200],
        ];
        foreach ($ranges as $upperLimit => $limits) {
            if ($maxLimit > $upperLimit
             && $upperLimit !== $upperEnd) {
                continue;
            }
            $lowerLimit = 0;
            foreach ($limits as $limit) {
                if ($minLimit <= $limit) {
                    $suggestions->push($this->getPriceRangeSuggestion($lowerLimit, $limit, $currency));
                    $lowerLimit = $limit;
                }
            }
            $suggestions->push($this->getPriceRangeSuggestion($lowerLimit, $upperLimit, $currency));
            break;
        }
        if ($maxLimit > $upperEnd) {
            $suggestions->push($this->getPriceRangeSuggestion($upperEnd, $maxLimit, $currency));
        }
        return $suggestions;
    }
    
    /**
     * Returns a price range suggesion object for the given data.
     * 
     * @param double $minPrice Minimum price
     * @param double $maxPrice Maximum price
     * @param string $currency Currency
     * 
     * @return ArrayData
     */
    protected function getPriceRangeSuggestion($minPrice, $maxPrice, $currency)
    {
        $upperEnd = $this->owner->config()->get('max_suggestion_limit');
        
        $minPriceNice = DBMoney::create()->setAmount($minPrice)->setCurrency(false)->NiceAmount();
        $maxPriceNice = DBMoney::create()->setAmount($maxPrice)->setCurrency(false)->NiceAmount();
        if ($minPrice === $upperEnd) {
            $title = _t(PriceFilterWidget::class . '.ShowOffersAbove', 'Show offers above {min} {currency}', [
                'min'      => $minPriceNice,
                'currency' => $currency,
            ]);
            $menuTitle = _t(PriceFilterWidget::class . '.OffersAbove', 'above {min} {currency}', [
                'min'      => $minPriceNice,
                'currency' => $currency,
            ]);
        } else {
            $title = _t(PriceFilterWidget::class . '.ShowOffersFromTo', 'Show offers from {min} to {max} {currency}', [
                'min'      => $minPriceNice,
                'max'      => $maxPriceNice,
                'currency' => $currency,
            ]);
            $menuTitle = "{$minPriceNice} - {$maxPriceNice} {$currency}";
        }
        return ArrayData::create([
            "Link"      => $this->owner->data()->PriceRangeLink($minPrice, $maxPrice),
            "Title"     => $title,
            "MenuTitle" => $menuTitle,
        ]);
    }
}