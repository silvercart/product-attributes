<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

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
    public function updateCacheKeyParts(&$cacheKeyParts)
    {
        $ctrl            = Controller::curr();
        $cacheKeyParts[] = sha1(implode('-', $ctrl->getFilterValues()));
    }
}