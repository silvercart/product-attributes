<?php

namespace SilverCart\ProductAttributes\Extensions\CustomerRebates;

use SilverStripe\ORM\DataExtension;

/**
 * Extension for SilverCart ProductAttributeValue.
 * Only relevant when using the customer rebates module.
 * 
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\CustomerRebates
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 13.12.2018
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class ProductAttributeValueExtension extends DataExtension
{
    /**
     * Many to many (n:m) back relations.
     *
     * @var array
     */
    private static $belongs_many_many = [
        'CustomerRebates' => 'SilverCart\CustomerRebates\Model\CustomerRebate',
    ];
    
    /**
     * Updates the field labels.
     * 
     * @param array &$labels Labels to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.12.2018
     */
    public function updateFieldLabels(&$labels) : void
    {
        $labels = array_merge($labels, [
            'CustomerRebates' => \SilverCart\CustomerRebates\Model\CustomerRebate::singleton()->plural_name(),
        ]);
    }
}