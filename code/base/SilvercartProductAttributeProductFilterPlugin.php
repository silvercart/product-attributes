<?php
/**
 * Copyright 2014 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage ProductFilterPlugins
 */

/**
 * Provides a view of items of a definable productgroup.
 *
 * @package Silvercart
 * @subpackage ProductFilterPlugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 22.03.2012
 * @license see license file in modules root directory
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeProductFilterPlugin {
    
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
     * @var SilvercartProductGroupPage_Controller 
     */
    protected $productGroup = null;
    
    /**
     * Returns the plugins filters
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2012 
     */
    public function filter() {
        if (self::$skip_filter) {
            return;
        }
        if (self::$skip_filter_once) {
            self::$skip_filter_once = false;
            return;
        }
        $filters        = array();
        if (Controller::curr() instanceof SilvercartProductGroupPage_Controller &&
            !Controller::curr()->isProductDetailView()) {
            $productGroup   = $this->getProductGroup();
            if ($productGroup->filterEnabled()) {
                $productIDs = $this->getProductIDs();
                if (count($productIDs) > 0) {
                    if (class_exists('SilvercartGroupBehaviorProductGroupPage_Controller')) {
                        SilvercartGroupBehaviorProductGroupPage_Controller::$disable_filter = true;
                    }
                    $filters['SilvercartProductAttributeProductFilterPlugin'] = sprintf(
                            'AND "SilvercartProduct"."ID" IN (%s)',
                            implode(',', $productIDs)
                    );
                }
            }
            $minPrice = Convert::raw2sql($productGroup->getMinPriceForWidget());
            $maxPrice = Convert::raw2sql($productGroup->getMaxPriceForWidget());
            $priceField = 'PriceGrossAmount';
            if (SilvercartConfig::Pricetype() == 'net') {
                $priceField = 'PriceNetAmount';
            }
            if (!empty($minPrice) &&
                !empty($maxPrice)) {
                $filters['SilvercartProductAttributeProductPriceFilterPlugin'] = sprintf(
                        'AND "SilvercartProduct"."%s" BETWEEN \'%s\' AND \'%s\'',
                        $priceField,
                        $minPrice,
                        $maxPrice
                );
            } elseif (!empty($minPrice)) {
                $filters['SilvercartProductAttributeProductPriceFilterPlugin'] = sprintf(
                        'AND "SilvercartProduct"."%s" >= \'%s\'',
                        $priceField,
                        $minPrice
                );
            } elseif (!empty($maxPrice)) {
                $filters['SilvercartProductAttributeProductPriceFilterPlugin'] = sprintf(
                        'AND "SilvercartProduct"."%s" <= \'%s\'',
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
     * @return SilvercartProductGroupPage_Controller
     */
    public function getProductGroup() {
        if (is_null($this->productGroup)) {
            $productGroup = Controller::curr();
            if (!$productGroup instanceof SilvercartProductGroupPage_Controller) {
                $productGroup = new SilvercartProductGroupPage_Controller();
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
        $productIDs     = array();
        $productGroup   = $this->getProductGroup();
        if ($productGroup->filterEnabled()) {
            $filterValues   = $productGroup->getFilterValues();
            if (is_array($filterValues) &&
                count($filterValues) > 0) {
                if (!(count($filterValues) == 1 &&
                    empty($filterValues[0]))) {
                    if ($productGroup->getWidget()->FilterBehaviour == 'MultipleChoice') {
                        $query = sprintf(
                                'SELECT DISTINCT
                                    "SPSPAV"."SilvercartProductID" AS PID
                                FROM
                                    "SilvercartProduct_SilvercartProductAttributeValues" AS SPSPAV
                                WHERE
                                    "SPSPAV"."SilvercartProductAttributeValueID" IN (%s)',
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
                                    'AND "SPSPAV"."SilvercartProductID" IN (%s)',
                                    implode(',', $productIDs)
                                );
                            }
                            $query = sprintf(
                                    'SELECT DISTINCT
                                        "SPSPAV"."SilvercartProductID" AS PID
                                    FROM
                                        "SilvercartProduct_SilvercartProductAttributeValues" AS SPSPAV
                                    WHERE
                                        "SPSPAV"."SilvercartProductAttributeValueID" = %s
                                    %s',
                                    $filterValue,
                                    $additionalWhereClause
                            );
                            $records = DB::query($query);
                            $productIDs = array();
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