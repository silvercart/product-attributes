<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverCart\Admin\Model\Config;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\ArrayData;

/**
 * Extension for SilverCart ProductGroupPage.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Extensions_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 23.08.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductGroupPageExtension extends DataExtension
{    
    /**
     * Adds a hash of the filter values to the product group cache key
     * 
     * @param array &$cacheKeyParts Cache key parts to update
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.08.2018
     */
    public function updateCacheKeyParts(array &$cacheKeyParts) : void
    {
        $ctrl            = Controller::curr();
        $cacheKeyParts[] = sha1(implode('-', $ctrl->getFilterValues()));
        if ($this->owner->isFilteredByPrice()) {
            $ctrl            = ModelAsController::controller_for($this->owner);
            $minPrice        = $ctrl->getMinPriceForWidget();
            $maxPrice        = $ctrl->getMaxPriceForWidget();
            $cacheKeyParts[] = "price-filter-{$minPrice}-{$maxPrice}";
        }
    }
    
    /**
     * Returns the link with filter parameters as HTTP GET parameters.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function FilteredLink() : string
    {
        $link = $this->owner->Link();
        if ($this->owner->isFilteredByPrice()) {
            $ctrl     = ModelAsController::controller_for($this->owner);
            $minPrice = $ctrl->getMinPriceForWidget();
            $maxPrice = $ctrl->getMaxPriceForWidget();
            $link     = $this->PriceRangeLink($minPrice, $maxPrice);
        }
        return (string) $link;
    }
    
    /**
     * Returns a price range link for the given $minPrice and $maxPrice.
     * 
     * @param double $minPrice Min price
     * @param double $maxPrice Max price
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function PriceRangeLink($minPrice, $maxPrice) : string
    {
        $link       = $this->owner->Link();
        $paramPairs = [];
        $params     = [
            'MinPrice' => $minPrice,
            'MaxPrice' => $maxPrice,
        ];
        foreach ($params as $name => $value) {
            $encodedValue = urlencode($value);
            $paramPairs[] = "{$name}={$encodedValue}";
        }
        $paramString = implode("&", $paramPairs);
        if (strpos($link, "?") === false) {
            $link .= "?{$paramString}";
        } else {
            $link .= "&{$paramString}";
        }
        return (string) $link;
    }
    
    /**
     * Updates the bread crumb items.
     * 
     * @param ArrayList $items Bread crumb items
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function updateBreadcrumbItems(ArrayList $items) : void
    {
        if ($this->owner->isFilteredByPrice()) {
            $ctrl = Controller::curr();
            if ($ctrl->hasMethod('getMinPriceForWidget')) {
                $currency = Config::DefaultCurrency();
                $title    = DBText::create();
                $title->setValue("{$ctrl->getMinPriceForWidget()} - {$ctrl->getMaxPriceForWidget()} {$currency}");
                $items->push(ArrayData::create([
                    'MenuTitle' => $title,
                    'Title'     => $title,
                    'Link'      => $this->owner->FilteredLink(),
                ]));
            }
        }
    }
    
    /**
     * Returns whether the product list is filtered by price.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function isFilteredByPrice() : bool
    {
        $isFilteredByPrice = false;
        $ctrl              = ModelAsController::controller_for($this->owner);
        $minPrice          = $ctrl->getMinPriceForWidget();
        $maxPrice          = $ctrl->getMaxPriceForWidget();
        if (is_numeric($minPrice)
         && is_numeric($maxPrice)
        ) {
            $isFilteredByPrice = true;
        }
        return $isFilteredByPrice;
    }
}