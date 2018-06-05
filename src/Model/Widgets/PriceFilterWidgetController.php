<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Widgets\WidgetController;
use SilverCart\ProductAttributes\Forms\PriceRangeForm;
use SilverStripe\Control\Controller;

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
}