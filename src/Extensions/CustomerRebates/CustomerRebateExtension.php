<?php

namespace SilverCart\ProductAttributes\Extensions\CustomerRebates;

use SilverCart\Model\Order\ShoppingCartPosition;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for SilverCart CustomerRebate.
 * Only relevant when using the customer rebates module.
 * 
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\CustomerRebates
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 13.12.2018
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomerRebateExtension extends DataExtension
{
    /**
     * Many to many (n:m) relations.
     *
     * @var array
     */
    private static $many_many = [
        'ProductAttributeValues' => ProductAttributeValue::class,
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
            'ProductAttributeValues' => ProductAttributeValue::singleton()->plural_name(),
        ]);
    }
    
    /**
     * Updates whether the given shopping cart position matches with the extended 
     * customer rebate.
     * 
     * @param ShoppingCartPosition $position    Shopping cart position
     * @param bool                 &$isMatching Original reference value to update
     * 
     * @return void
     */
    public function updatePositionIsMatchingWithRebate(ShoppingCartPosition $position, bool &$isMatching) : void
    {
        $rebateAttributeValues = $this->owner->ProductAttributeValues();
        if ($isMatching
         && $rebateAttributeValues->exists()
        ) {
            $isMatching = false;
            $product    = $position->Product();
            /* @var $product \SilverCart\Model\Product\Product */
            foreach ($rebateAttributeValues as $attributeValue) {
                $relatedValue = $product->ProductAttributeValues()->byID($attributeValue->ID);
                if ($relatedValue instanceof ProductAttributeValue
                 && $relatedValue->exists()
                ) {
                    $isMatching = true;
                    break;
                }
            }
        }
    }
}