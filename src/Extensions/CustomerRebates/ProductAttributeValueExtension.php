<?php

namespace SilverCart\ProductAttributes\Extensions\CustomerRebates;

use SilverCart\CustomerRebates\Model\CustomerRebate;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
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
 * 
 * @property ProductAttributeValue $owner Owner
 */
class ProductAttributeValueExtension extends DataExtension
{
    /**
     * Many to many (n:m) back relations.
     *
     * @var array
     */
    private static array $belongs_many_many = [
        'CustomerRebates' => CustomerRebate::class . '.ProductAttributeValues',
    ];
    
    /**
     * Updates the field labels.
     * 
     * @param array &$labels Labels to update
     * 
     * @return void
     */
    public function updateFieldLabels(&$labels) : void
    {
        $labels = array_merge($labels, [
            'CustomerRebates' => CustomerRebate::singleton()->i18n_plural_name(),
        ]);
    }
}