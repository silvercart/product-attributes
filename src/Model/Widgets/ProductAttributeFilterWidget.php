<?php

namespace SilverCart\ProductAttributes\Model\Widgets;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Widgets\Widget;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\CheckboxField;

/**
 * Provides a view of items of a definable productgroup.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductAttributeFilterWidget extends Widget {
    
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'FilterBehaviour' => 'Enum("MultipleChoice,SingleChoice","MultipleChoice")',
        'RememberFilter'  => 'Boolean(0)',
    ];
    
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductAttributeFilterWidget';
    
    /**
     * Javascript selector to load content into.
     *
     * @var string
     */
    private static $js_main_selector = '#main';

    /**
     * Returns the JS selector.
     * 
     * @return string
     */
    public static function get_js_main_selector() {
        return self::$js_main_selector;
    }

    /**
     * Sets the JS selector.
     * 
     * @param string $js_main_selector JS main selector
     */
    public static function set_js_main_selector($js_main_selector) {
        self::$js_main_selector = $js_main_selector;
    }

    /**
     * Returns the title of this widget.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Title() {
        return $this->fieldLabel('WidgetTitle');
    }
    
    /**
     * Returns the title of this widget for display in the WidgetArea GUI.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function CMSTitle() {
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
    public function Description() {
        return $this->fieldLabel('WidgetDescription');
    }
    
    /**
     * Returns the extra css classes.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.04.2013
     */
    public function ExtraCssClasses() {
        return $this->dbObject('ExtraCssClasses')->getValue() . ' silvercart-product-attribute-filter-widget';
    }

    /**
     * Returns the widgets content
     *
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function Content() {
        $content = false;
        if (Controller::curr() instanceof ProductGroupPageController &&
            !Controller::curr()->isProductDetailView()) {
            $content = parent::Content();
        }
        return Tools::string2html($content);
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
    public function fieldLabels($includerelations = true) {
        return array_merge(
                parent::fieldLabels($includerelations),
                Tools::field_labels_for(static::class),
                [
                    'FilterBehaviour'     => _t(static::class . '.FILTERBEHAVIOUR', 'Filter-Behavior'),
                    'FilterBehaviourDesc' => _t(static::class . '.FB_HINT', 'Filter-Behavior.'),
                    'RememberFilter'      => _t(static::class . '.REMEMBERFILTER', 'Remember filter by product group'),
                    'WidgetTitle'         => _t(static::class . '.TITLE', 'Product Attribute Filter'),
                    'WidgetCMSTitle'      => _t(static::class . '.CMSTITLE', 'Product Attribute Filter'),
                    'WidgetDescription'   => _t(static::class . '.DESCRIPTION', 'Provides a grouped selection of filters, created by the product attributes assigned onto the products of the current product group.'),
                ]
        );
    }
    
    /**
     * CMS fields for this widget
     *
     * @return FieldList
     */
    public function getCMSFields() {
        $fields                 = parent::getCMSFields();
        $filterBehaviourDbField = $this->dbObject('FilterBehaviour');
        $enumValues             = $filterBehaviourDbField->enumValues();
        $items                  = [];
        foreach ($enumValues as $key => $value) {
            $items[$key] = _t(static::class . '.FB_' . strtoupper($value), $value);
        }
        $filterBehaviourField = new OptionsetField('FilterBehaviour', $this->fieldLabel('FilterBehaviour'), $items, $this->FilterBehaviour);
        $filterBehaviourField->setRightTitle(Tools::string2html($this->fieldLabel('FilterBehaviourDesc')));
        $fields->addFieldToTab('Root.Main', $filterBehaviourField);
        
        $rememberFilterField = new CheckboxField('RememberFilter', $this->fieldLabel('RememberFilter'));
        $fields->addFieldToTab('Root.Main', $rememberFilterField);
        return $fields;
    }
}