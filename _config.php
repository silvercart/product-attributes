<?php

use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Pages\SearchResultsPageController;
use SilverCart\Model\Widgets\Widget;
use SilverCart\ProductAttributes\Plugins\ProductFilterPlugin;

ProductGroupPageController::registerFilterPlugin(ProductFilterPlugin::class);
SearchResultsPageController::registerFilterPlugin(ProductFilterPlugin::class);
Widget::registerFilterPlugin(ProductFilterPlugin::class);