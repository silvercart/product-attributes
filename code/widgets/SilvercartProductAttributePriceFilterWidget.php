<?php
/**
 * Copyright 2012 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Silvercart
 * @subpackage Widgets
 */

/**
 * Provides a widget to filter a product list by a price range
 *
 * @package Silvercart
 * @subpackage Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributePriceFilterWidget extends SilvercartWidget {

    /**
     * Returns the title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function Title() {
        return _t('SilvercartProductAttributePriceFilterWidget.TITLE');
    }

    /**
     * Returns the front title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function FrontTitle() {
        return _t('SilvercartProductAttributePriceFilterWidget.FRONTTITLE');
    }
    
    /**
     * Returns the title of this widget for display in the WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function CMSTitle() {
        return _t('SilvercartProductAttributePriceFilterWidget.CMSTITLE');
    }
    
    /**
     * Returns the description of what this template does for display in the
     * WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012
     */
    public function Description() {
        return _t('SilvercartProductAttributePriceFilterWidget.DESCRIPTION');
    }
    
    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $content = parent::Content();
        }
        return $content;
    }
}

/**
 * Provides a widget to filter a product list by a price range
 *
 * @package Silvercart
 * @subpackage Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartProductAttributePriceFilterWidget_Controller extends SilvercartWidget_Controller {
    
    /**
     * Form action for filter form
     *
     * @var string
     */
    protected $formAction = null;

    /**
     * Initializes the widget controller
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.03.2012 
     */
    public function init() {
        parent::init();
        
        Controller::curr()->registerCustomHtmlForm(
            'SilvercartProductAttributePriceRangeForm',
            new SilvercartProductAttributePriceRangeForm(
                Controller::curr()
            )
        );
    }
    
    /**
     * Returns the HTML code for the search form.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function InsertCustomHtmlForm() {
        return Controller::curr()->InsertCustomHtmlForm('SilvercartProductAttributePriceRangeForm');
    }

    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
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