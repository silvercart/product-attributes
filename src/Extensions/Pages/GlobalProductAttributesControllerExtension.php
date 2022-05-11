<?php

namespace SilverCart\ProductAttributes\Extensions\Pages;

use SilverCart\Model\Pages\CartPage;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataList;

/**
 * Extension for any PageController using the RequestInProductGroups feature.
 *
 * @package SilverCart
 * @subpackage ProductAttributes\Extensions\Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 10.03.2022
 * @license see license file in modules root directory
 * @copyright 2022 pixeltricks GmbH
 * 
 * @property \SilverCart\Model\Pages\PageController $owner Owner
 */
class GlobalProductAttributesControllerExtension extends Extension
{
    use \SilverStripe\Core\Config\Configurable;
    /**
     * List of class names to skip modal for.
     * 
     * @var string[]
     */
    private static $skip_show_modal_for = [
        CartPage::class,
    ];
    /**
     * Returns whether the global attributes are required.
     * 
     * @var bool
     */
    private static $global_product_attributes_required = true;
    /**
     * Reload the page after choosing the global attribute?
     * 
     * @var bool
     */
    private static $reload_page_after_choose_global_product_attributes_modal = false;
    
    /**
     * Returns all product attributes to request in product groups.
     * 
     * @return DataList
     */
    public function ProductAttributeRequestInProductGroupsItems() : DataList
    {
        return ProductAttribute::get()->filter('RequestInProductGroups', true);
    }
    
    /**
     * Returns the first product attribute to request in product groups.
     * 
     * @return ProductAttribute|NULL
     */
    public function ProductAttributeRequestInProductGroupsItem() : ?ProductAttribute
    {
        return $this->ProductAttributeRequestInProductGroupsItems()->first();
    }

    /**
     * Returns whether to show the modal to choose a lobal product attribute.
     * 
     * @return bool
     */
    public function ShowChooseGlobalProductAttributesModal() : bool
    {
        $show = Controller::has_curr()
             && Controller::curr()->getRequest()->getVar('scpasm') === '1';
        $referer = $this->owner->RefererPage($this->owner->getReturnReferer());
        if ($referer instanceof SiteTree
         && in_array($referer->ClassName, $this->config()->skip_show_modal_for)
        ) {
            return false;
        }
        if (Controller::has_curr()) {
            $controller = Controller::curr();
            if ($controller->hasMethod('isProductDetailView')
             && $controller->isProductDetailView()
             && $controller->getDetailViewProduct()->isInCart()
            ) {
                return false;   
            }
        }
        if (!$show
         && $this->owner->config()->global_product_attributes_required
        ) {
            $item = $this->ProductAttributeRequestInProductGroupsItem();
            if ($item !== null
             && !$item->HasGloballyChosenValues()
            ) {
                $show = true;
            }
        }
        return $show;
    }
    
    /**
     * Returns whether to reload the page after choosing the global attribute.
     * 
     * @return bool
     */
    public function ReloadPageAfterChooseGlobalProductAttributesModal() : bool
    {
        return (bool) $this->owner->config()->reload_page_after_choose_global_product_attributes_modal;
    }
}