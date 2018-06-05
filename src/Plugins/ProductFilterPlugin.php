<?php

namespace SilverCart\ProductAttributes\Plugins;

use SilverCart\Admin\Model\Config;
use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPageController;
use SilverCart\Model\Product\Product;
use SilverCart\ProductAttributes\Model\Product\ProductAttributeValue;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DB;

/**
 * Provides a view of items of a definable productgroup.
 *
 * @package SilverCart
 * @subpackage ProductAttributes_Plugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.05.2018
 * @license see license file in modules root directory
 * @copyright 2018 pixeltricks GmbH
 */
class ProductFilterPlugin {
    
    /**
     * Set this to true to skip filter
     *
     * @var bool
     */
    public static $skip_filter = false;
    
    /**
     * Set this to true to skip filter once
     *
     * @var bool
     */
    public static $skip_filter_once = false;

    /**
     *
     * @var ProductGroupPageController 
     */
    protected $productGroup = null;
    
    /**
     * Returns the plugins filters
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.05.2018
     */
    public function filter() {
        if (self::$skip_filter) {
            return;
        }
        if (self::$skip_filter_once) {
            self::$skip_filter_once = false;
            return;
        }
        $filters = [];
        if (Controller::curr() instanceof ProductGroupPageController &&
            !Controller::curr()->isProductDetailView()) {
            $productTableName = Tools::get_table_name(Product::class);
            $productGroup     = $this->getProductGroup();
            if ($productGroup->filterEnabled()) {
                $productIDs = $this->getProductIDs();
                if (count($productIDs) > 0) {
                    $groupBehaviorController = "SilverCart\\GroupBehavior\\Model\\Pages\\ProductGroupPageController";
                    if (class_exists($groupBehaviorController)) {
                        $groupBehaviorController::$disable_filter = true;
                    }
                    $filters[static::class] = sprintf(
                            'AND "%s"."ID" IN (%s)',
                            $productTableName,
                            implode(',', $productIDs)
                    );
                }
            }
            $minPrice = Convert::raw2sql($productGroup->getMinPriceForWidget());
            $maxPrice = Convert::raw2sql($productGroup->getMaxPriceForWidget());
            $priceField = 'PriceGrossAmount';
            if (Config::Pricetype() == 'net') {
                $priceField = 'PriceNetAmount';
            }
            if (!empty($minPrice) &&
                !empty($maxPrice)) {
                $filters[static::class] = sprintf(
                        'AND "%s"."%s" BETWEEN \'%s\' AND \'%s\'',
                        $productTableName,
                        $priceField,
                        $minPrice,
                        $maxPrice
                );
            } elseif (!empty($minPrice)) {
                $filters[static::class] = sprintf(
                        'AND "%s"."%s" >= \'%s\'',
                        $productTableName,
                        $priceField,
                        $minPrice
                );
            } elseif (!empty($maxPrice)) {
                $filters[static::class] = sprintf(
                        'AND "%s"."%s" <= \'%s\'',
                        $productTableName,
                        $priceField,
                        $maxPrice
                );
            }
        }
        return $filters;
    }

    /**
     * Returns the controller of the current product group
     *
     * @return ProductGroupPageController
     */
    public function getProductGroup() {
        if (is_null($this->productGroup)) {
            $productGroup = Controller::curr();
            if (!$productGroup instanceof ProductGroupPageController) {
                $productGroup = new ProductGroupPageController();
                $productGroup->permanentlyDisableFilter();
            }
            $this->productGroup = $productGroup;
        }
        return $this->productGroup;
    }
    
    /**
     * Returns the filtered product IDs
     *
     * @return array
     */
    public function getProductIDs() {
        $productIDs   = [];
        $productGroup = $this->getProductGroup();
        if ($productGroup->filterEnabled()) {
            $filterValues = $productGroup->getFilterValues();
            if (is_array($filterValues) &&
                count($filterValues) > 0) {
                if (!(count($filterValues) == 1 &&
                    empty($filterValues[0]))) {
                    $productTableName               = Tools::get_table_name(Product::class);
                    $productAttributeValueTableName = Tools::get_table_name(ProductAttributeValue::class);
                    if ($productGroup->getWidget()->FilterBehaviour == 'MultipleChoice') {
                        $query = sprintf(
                                'SELECT DISTINCT
                                    "SPSPAV"."%sID" AS PID
                                FROM
                                    "%s_ProductAttributeValues" AS SPSPAV
                                WHERE
                                    "SPSPAV".%sID" IN (%s)',
                                $productTableName,
                                $productTableName,
                                $productAttributeValueTableName,
                                "'" . implode("','", $filterValues) . "'"
                        );
                        $records = DB::query($query);
                        foreach ($records as $record) {
                            $productIDs[] = $record['PID'];
                        }
                    } elseif ($productGroup->getWidget()->FilterBehaviour == 'SingleChoice') {
                        foreach ($filterValues as $filterValue) {
                            if (empty($filterValue)) {
                                continue;
                            }
                            $additionalWhereClause = "";
                            if (count($productIDs) > 0) {
                                $additionalWhereClause = sprintf(
                                    'AND "SPSPAV"."%sID" IN (%s)',
                                    $productTableName,
                                    implode(',', $productIDs)
                                );
                            }
                            $query = sprintf(
                                    'SELECT DISTINCT
                                        "SPSPAV"."%sID" AS PID
                                    FROM
                                        "%s_ProductAttributeValues" AS SPSPAV
                                    WHERE
                                        "SPSPAV"."%sID" = %s
                                    %s',
                                    $productTableName,
                                    $productTableName,
                                    $productAttributeValueTableName,
                                    $filterValue,
                                    $additionalWhereClause
                            );
                            $records = DB::query($query);
                            $productIDs = [];
                            foreach ($records as $record) {
                                $productIDs[] = $record['PID'];
                            }
                        }
                    }
                }
            }
        }
        return $productIDs;
    }
    
}