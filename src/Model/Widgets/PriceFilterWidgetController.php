<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Pages\SearchResultsPageController;
use SilverCart\Model\Widgets\WidgetController;
use SilverCart\ProductAttributes\Forms\PriceRangeForm;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;

/**
 * Provides a widget to filter a product list by a price range
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class PriceFilterWidgetController extends WidgetController {
    
    use \SilverCart\ProductAttributes\Control\PriceRangeController;
    
    /**
     * List of allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'PriceRangeForm',
    ];

    /**
     * Form action for filter form
     *
     * @var string
     */
    protected $formAction = null;
    
    /**
     * Initializes the price filter.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    protected function init() {
        parent::init();
        $this->initPriceFilterFromRequest($this->getRequest());
    }
    
    /**
     * Returns the HTML code for the search form.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function PriceRangeForm() {
        $form = new PriceRangeForm($this);
        return $form;
    }

    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof ProductGroupPageController &&
            !Controller::curr()->isProductDetailView() &&
            Controller::curr()->HasMoreProductsThan(0)) {
            $content = parent::Content();
        }
        return $content;
    }
    
    /**
     * Returns the form action
     *
     * @return string
     */
    public function getFormAction() {
        return $this->formAction;
    }
    
    /**
     * Sets the form action
     *
     * @param string $formAction form action
     * 
     * @return void
     */
    public function setFormAction($formAction) {
        $this->formAction = $formAction;
    }
    
    /**
     * Builds and returns the session key dependant on the controller type
     *
     * @return string 
     */
    public function getSessionKey() {
        $sessionKey = null;
        $controller = $this->getParentController();
        if ($controller instanceof \SilverStripe\CMS\Controllers\ContentController) {
            $sessionKey = $controller->data()->ID;
            if ($controller instanceof SearchResultsPageController) {
                $searchQuery = Convert::raw2sql(Tools::Session()->get(SearchResultsPageController::SESSION_KEY_SEARCH_QUERY));
                $sessionKey .= md5($searchQuery) . sha1($searchQuery);
            }
        }
        return $sessionKey;
    }
    
}