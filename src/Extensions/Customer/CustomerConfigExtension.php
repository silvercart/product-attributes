<?php

namespace SilverCart\ProductAttributes\Extensions\Customer;

use SilverCart\Model\Customer\CustomerConfig;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for SilverCart CustomerConfig.
 *
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\Customer
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 10.01.2022
 * @license see license file in modules root directory
 * @copyright 2022 pixeltricks GmbH
 * 
 * @property \SilverCart\Model\Customer\CustomerConfig $owner Owner
 */
class CustomerConfigExtension extends DataExtension
{
    /**
     * DB attributes.
     * 
     * @var string[]
     */
    private static $db = [
        'ProductAttributeSettings' => 'Text',
    ];
    
    /**
     * Returns the ProductAttributeSettings as array.
     * 
     * @return array
     */
    public function getProductAttributeSettingsToArray() : array
    {
        return (array) unserialize((string) $this->owner->ProductAttributeSettings);
    }
    
    /**
     * Writes the ProductAttributeSettings.
     * 
     * @param array $data Data to write
     * 
     * @return CustomerConfig
     */
    public function writeProductAttributeSettings(array $data) : CustomerConfig
    {
        $this->owner->ProductAttributeSettings = serialize($data);
        $this->owner->write();
        return $this->owner;
    }
}