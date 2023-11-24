<?php

namespace SilverCart\ProductAttributes\Control;

use Page;
use SilverCart\Deeplink\Model\Pages\DeeplinkPage;
use SilverCart\Deeplink\Model\Product\Deeplink;
use SilverCart\Dev\Tools;
use SilverCart\Model\Order\ShoppingCart;
use SilverCart\Model\Product\Product;
use SilverCart\View\GroupView\GroupViewExtension;
use SilverStripe\Control\Controller as SilverStripeController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\DataList;

/**
 * Main controller for the ProductAttributes feature.
 *
 * @package SilverCart
 * @subpackage ProductAttributes\Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 20.11.2023
 * @copyright 2023 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class Controller extends SilverStripeController
{
    use \SilverCart\Model\Pages\PageControllable;
    /**
     * URL segment
     *
     * @var string
     */
    private static $url_segment = 'sc-pa';
    /**
     * Allowed actions.
     *
     * @var string[]
     */
    private static $allowed_actions = [
        'load_variant_fields',
    ];
    
    /**
     * Loads the variant form fields for the requested product ID.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return HTTPResponse
     */
    public function load_variant_fields(HTTPRequest $request) : HTTPResponse
    {
        if ($request->param('ID') === null) {
            $this->httpError(404);
        }
        $product = Product::get()->byID($request->param('ID'));
        if ($product === null
         || !$product->isActive
         || !$product->canView()
        ) {
            $this->httpError(404);
        }
        return HTTPResponse::create($product->forTemplate('VariantFields'));
    }
}