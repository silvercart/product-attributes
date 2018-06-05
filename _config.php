<?php

use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Pages\SearchResultsPageController;
use SilverCart\Model\Widgets\WidgetController;
use SilverCart\ProductAttributes\Plugins\ProductFilterPlugin;

ProductGroupPageController::registerFilterPlugin(ProductFilterPlugin::class);
SearchResultsPageController::registerFilterPlugin(ProductFilterPlugin::class);
WidgetController::registerFilterPlugin(ProductFilterPlugin::class);