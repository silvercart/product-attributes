<?php

use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Pages\SearchResultsPageController;
use SilverCart\Model\Widgets\Widget;
use SilverCart\ProductAttributes\Extensions\CustomerRebates\CustomerRebateExtension;
use SilverCart\ProductAttributes\Extensions\CustomerRebates\ProductAttributeValueExtension as CustomerRebateProductAttributeValueExtension;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverCart\ProductAttributes\Plugins\ProductFilterPlugin;

ProductGroupPageController::registerFilterPlugin(ProductFilterPlugin::class);
SearchResultsPageController::registerFilterPlugin(ProductFilterPlugin::class);
Widget::registerFilterPlugin(ProductFilterPlugin::class);

if (class_exists('SilverCart\CustomerRebates\Model\CustomerRebate')) {
    SilverCart\CustomerRebates\Model\CustomerRebate::add_extension(CustomerRebateExtension::class);
    ProductAttributeValue::add_extension(CustomerRebateProductAttributeValueExtension::class);
}