<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Widgets\Widget;
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
class PriceFilterWidget extends Widget
{
    use \SilverCart\ORM\ExtensibleDataObject;
    
    /**
     * Returns the title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Title()
    {
        return $this->fieldLabel('WidgetTitle');
    }

    /**
     * Returns the front title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function FrontTitle()
    {
        return $this->fieldLabel('WidgetFrontTitle');
    }
    
    /**
     * Returns the title of this widget for display in the WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function CMSTitle()
    {
        return $this->fieldLabel('WidgetCMSTitle');
    }
    
    /**
     * Returns the description of what this template does for display in the
     * WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Description()
    {
        return $this->fieldLabel('WidgetDescription');
    }
    
    /**
     * Returns the widgets content
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.03.2012
     */
    public function Content()
    {
        $content = false;
        if (Controller::curr() instanceof ProductGroupPageController
         && !Controller::curr()->isProductDetailView()
        ) {
            $content = parent::Content();
        }
        return $content;
    }

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function fieldLabels($includerelations = true)
    {
        $this->beforeUpdateFieldLabels(function(&$labels) {
            $labels = array_merge(
                    $labels,
                    Tools::field_labels_for(static::class),
                    [
                        'Go'                => _t('SilverCart.Go', 'Go'),
                        'Filter'            => _t(static::class . '.FILTER', 'Filter'),
                        'MinPrice'          => _t(static::class . '.MIN_PRICE', 'From'),
                        'MaxPrice'          => _t(static::class . '.MAX_PRICE', 'to'),
                        'WidgetTitle'       => _t(static::class . '.TITLE', 'Price Filter'),
                        'WidgetFrontTitle'  => _t(static::class . '.FRONTTITLE', 'Filter by price'),
                        'WidgetCMSTitle'    => _t(static::class . '.CMSTITLE', 'Price Filter'),
                        'WidgetDescription' => _t(static::class . '.DESCRIPTION', 'Provides a widget which allows to enter a price range to filter a product list.'),
                    ]
            );
        });
        return parent::fieldLabels($includerelations);
    }
}