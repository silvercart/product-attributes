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
 * @subpackage ProductFilterPlugins
 */

/**
 * Provides a view of items of a definable productgroup.
 *
 * @package Silvercart
 * @subpackage ProductFilterPlugins
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 22.03.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2012 pixeltricks GmbH
 */
class SilvercartProductAttributeProductFilterPlugin {
    
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
        $filters        = array();
        $productGroup   = $this->getProductGroup();
        if ($productGroup->filterEnabled()) {
            $productIDs = $this->getProductIDs();
            if (count($productIDs) > 0) {
                $filters['SilvercartProductAttributeProductFilterPlugin'] = sprintf(
                        "AND `SilvercartProduct`.`ID` IN (%s)",
                        implode(',', $productIDs)
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
                                "SELECT DISTINCT
                                    SPSPAV.`SilvercartProductID` AS PID
                                FROM
                                    `SilvercartProduct_SilvercartProductAttributeValues` AS SPSPAV
                                WHERE
                                    SPSPAV.`SilvercartProductAttributeValueID` IN (%s)",
                                implode(',', $filterValues)
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
                                    "AND
                                        SPSPAV.`SilvercartProductID` IN (%s)",
                                    implode(',', $productIDs)
                                );
                            }
                            $query = sprintf(
                                    "SELECT DISTINCT
                                        SPSPAV.`SilvercartProductID` AS PID
                                    FROM
                                        `SilvercartProduct_SilvercartProductAttributeValues` AS SPSPAV
                                    WHERE
                                        SPSPAV.`SilvercartProductAttributeValueID` = %s
                                    %s",
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