<?php

namespace SilverCart\ProductAttributes\Forms;

use SilverCart\Admin\Model\Config;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Forms\CustomForm;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\ORM\FieldType\DBMoney;
use SilverCart\ProductAttributes\Model\Widgets\PriceFilterWidget;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FormAction;

/**
 * Form to enter a price range.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class PriceRangeForm extends CustomForm
{
    /**
     * Don't enable Security token for this type of form because we'll run
     * into caching problems when using it.
     * 
     * @var boolean
     */
    protected $securityTokenEnabled = false;
    /**
     * List of required fields.
     *
     * @var array
     */
    private static $requiredFields = [
        'MinPrice',
        'MaxPrice',
    ];

    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomFields()
    {
        $this->beforeUpdateCustomFields(function (array &$fields) {
            $minPrice = '';
            $maxPrice = '';
            $widget   = PriceFilterWidget::singleton();
            
            if ($this->getCurrentController() instanceof ProductGroupPageController
             && !$this->getCurrentController()->isProductDetailView()
            ) {
                $minPrice = $this->getCurrentController()->getMinPriceForWidget();
                $maxPrice = $this->getCurrentController()->getMaxPriceForWidget();
            }
            
            $fields = array_merge(
                    $fields,
                    [
                        TextField::create('MinPrice', $widget->fieldLabel('MinPrice'), $minPrice)->setPlaceholder($widget->fieldLabel('MinPrice')),
                        TextField::create('MaxPrice', $widget->fieldLabel('MaxPrice'), $maxPrice)->setPlaceholder($widget->fieldLabel('MaxPrice')),
                    ]
            );
        });
        
        return parent::getCustomFields();
    }
    
    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomActions()
    {
        $this->beforeUpdateCustomActions(function (array &$actions) {
            $actions += [
                FormAction::create('submit', PriceFilterWidget::singleton()->fieldLabel('Go'))
                    ->setUseButtonTag(true)->addExtraClass('btn-primary')
            ];
        });
        return parent::getCustomActions();
    }
    
    /**
     * Submits the form.
     * 
     * @param array      $data Submitted data
     * @param CustomForm $form Form
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2018
     */
    public function doSubmit($data, CustomForm $form)
    {
        $ctrl   = $this->getCurrentController();
        $ctrl->setMinPriceForWidget($data['MinPrice']);
        $ctrl->setMaxPriceForWidget($data['MaxPrice']);
        $widget = $ctrl->data();
        $link   = str_replace("widgetset/{$widget->ID}", "", $ctrl->Link());
        $page   = SiteTree::get_by_link($link);
        if ($page instanceof SiteTree) {
            $ctrl->redirect($page->FilteredLink());
        } else {
            $ctrl->redirectBack();
        }
    }
    
    /**
     * Returns the cache key extension for this form
     * 
     * @return string
     */
    public function getCacheKeyExtension()
    {
        return md5($this->getCurrentController()->data()->ID . '-' . $this->getCurrentController()->getMinPriceForWidget() . '-' . $this->getCurrentController()->getMaxPriceForWidget());
    }
    
    /**
     * Returns the currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return Config::DefaultCurrency();
    }
    
    /**
     * Returns the currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return DBMoney::create()->setCurrency($this->getCurrency())->getSymbol();
    }
    
    /**
     * Returns the current controller.
     * 
     * @return Controller
     */
    public function getCurrentController()
    {
        return Controller::curr();
    }
}