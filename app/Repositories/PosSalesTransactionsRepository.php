<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosSalesTransactionsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosSalesTransactions';
    }

    /**
     * Returns the total sale for the licenses
     * @param $licenses
     * @return mixed
     */
    public function getTotalSales($licenses, $start="", $end="")
    {
        $licenses = str_replace(",", "','", $licenses);
        $date_between = ((empty($start)||empty($end))?"":"AND SalesDate between date('$start') and date('$end')");
        $result = DB::connection('mysql_external_intake')->select(DB::raw("
            SELECT SUM(pos_sales_transactions.TotalPrice) as sales, min(SalesDate) as start, max(SalesDate) as end
            FROM `pos_sales_transactions`
            WHERE pos_sales_transactions.LicenseNumber in ('$licenses')
            $date_between;
        "));
        \Log::info("==== PosSalesTransactionsRepository->getTotalSales ", ['u' => json_encode($result)]);
        return $result;
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getProductPrices($licenses, $start="", $end="", $order="DESC")
    {
        $licenses = str_replace(",","','",$licenses);
        $date_between = ((empty($start)||empty($end))?"":"AND SalesDate between date('$start') and date('$end')");
        $result = DB::connection('mysql_external_intake')->select( DB::raw("
            (
                SELECT SUM(TotalPrice) as TotalPrice, LicenseNumber, LOWER(ProductName) as ProductName
                FROM `pos_sales_transactions`
                WHERE ProductName IN (
                    SELECT LOWER(ProductName) FROM (
                        SELECT SUM(TotalPrice) as TotalPrice, LOWER(ProductName) as ProductName
                        FROM `pos_sales_transactions`
                        WHERE LicenseNumber IN ('$licenses')
                        $date_between
                        GROUP BY ProductName
                        ORDER BY TotalPrice $order
                        LIMIT 10
                    ) bestOverall
                )
                AND LicenseNumber IN ('$licenses')
                GROUP BY ProductName, LicenseNumber
                ORDER BY LicenseNumber ASC
            )
            UNION
            (
                SELECT AVG(Summ.TotalPrice) as TotalPrice, 'National Average' as LicenseNumber, LOWER(Summ.ProductName) as ProductName
                FROM (
                    SELECT SUM(TotalPrice) as TotalPrice, LOWER(ProductName) as ProductName, LicenseNumber
                    FROM `pos_sales_transactions`
                    WHERE LOWER(ProductName) IN (
                        SELECT LOWER(ProductName) FROM (
                            SELECT SUM(TotalPrice) as TotalPrice, LOWER(ProductName) as ProductName
                            FROM `pos_sales_transactions`
                            WHERE LicenseNumber IN ('$licenses')
                            $date_between
                            GROUP BY ProductName
                            ORDER BY TotalPrice $order
                            LIMIT 10
                        ) bestOverall
                    )
                    GROUP BY ProductName, LicenseNumber
                ) as Summ
                GROUP BY ProductName
            );
        ") );

        $ProductPricingList = array();
        // Insert pricing for all licenses that have a price
        foreach($result as $line) {
            if($line->LicenseNumber != "National Average") {
                $element['name'] = $line->LicenseNumber;
                $element['value'] = $line->TotalPrice;
                $ProductPricingList[trim($line->ProductName)][] = $element;
            }
        }

        $licenses = explode(",", str_replace("'","", $licenses));
        // Insert pricing as 0 for all licenses that doesn't have a price
        foreach($ProductPricingList as $productName=>$pricings) {
            $license_list = array_column($ProductPricingList[$productName], 'name');
            foreach($licenses as $license) {
                if(!in_array($license, $license_list)) {
                    $element['name'] = $license;
                    $element['value'] = 0;
                    $ProductPricingList[$productName][] = $element;
                }
            }
        }

        // Insert pricing for the National Average
        foreach($result as $line) {
            if($line->LicenseNumber == "National Average") {
                $element['name'] = $line->LicenseNumber;
                $element['value'] = $line->TotalPrice;
                $ProductPricingList[trim($line->ProductName)][] = $element;
            }
        }

        // Insert the pricing into the product list
        $productList = array();
        foreach($ProductPricingList as $key=>$value) {
            $productElement['name'] = $key;
            $productElement['series'] = $value;
            $productList[] = $productElement;
        }
        return $productList;
    }

    public function sort_by_name($a,$b) {
        return ($a["name"] <= $b["name"]) ? -1 : 1;
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getMonthlyRevenue($licenses, $start="", $end="")
    {
        $licenses = str_replace(",","','",$licenses);
        $first_date_between = ((empty($start)||empty($end))?"":"AND SalesDate between date('$start') and date('$end')");
        $second_date_between = ((empty($start)||empty($end))?"":"WHERE SalesDate between date('$start') and date('$end')");
        $result = DB::connection('mysql_external_intake')->select( DB::raw("
            (
                SELECT CONCAT(YEAR(SalesDate),'-', LPAD(MONTH(SalesDate), 2, '0')) as Month, LicenseNumber, SUM(TotalPrice) as Revenue
                FROM `pos_sales_transactions` 
                WHERE LicenseNumber IN ('$licenses')
                $first_date_between
                GROUP BY Month, LicenseNumber
                ORDER BY LicenseNumber ASC
            )
            UNION
            (
                SELECT Summ.Month as Month, 'National Average' as LicenseNumber, AVG(Revenue) as Revenue
                FROM (
                    SELECT CONCAT(YEAR(SalesDate),'-', LPAD(MONTH(SalesDate), 2, '0')) as Month, SUM(TotalPrice) as Revenue, LicenseNumber
                    FROM `pos_sales_transactions` 
                    $second_date_between
                    GROUP BY Month, LicenseNumber
                ) as Summ
                GROUP BY Summ.Month
            )
            ORDER BY Month ASC;
        ") );

        // Push month=>totalRevenue in multi array
        $multiVector = array();
        $singleVector = array();
        $months = array();
        foreach($result as $line){
            $element = array();
            $months[] = $line->Month;
            $element['name'] = $line->Month;
            $element['value'] = $line->Revenue;
            $multiVector[trim($line->LicenseNumber)][] = $element;
            if(array_key_exists ( trim($line->LicenseNumber) , $singleVector )) {
                $singleVector[trim($line->LicenseNumber)] += $line->Revenue;
            }
            else {
                $singleVector[trim($line->LicenseNumber)] = $line->Revenue;
            }
        }

        // Sort by license alphabetic order with National Average at the end
        $v = $singleVector['National Average'];
        unset($singleVector['National Average']);
        asort($singleVector);
        $singleVector['National Average'] = $v;


        // Fill the gap in the dates in order to have a full revenue calendar
        $months = array_unique($months);
        foreach ($multiVector as $license=>$list) {
            $list_months = array_column($list, 'name');
            foreach ($months as $month) {
                if(!in_array($month, $list_months)) {
                    $element['name'] = $month;
                    $element['value'] = 0;
                    $multiVector[$license][] = $element;
                }
            }
            usort($multiVector[$license], array($this, "sort_by_name"));
        }

        // Sort by license alphabetic order with National Average at the end
        $v = $multiVector['National Average'];
        unset($multiVector['National Average']);
        asort($multiVector);
        $multiVector['National Average'] = $v;

        // Push licence=>serie(month=>totalRevenue) in multi array
        $multiRevenue = array();
        foreach($multiVector as $key=>$value) {
            $month_element = array();
            $month_element['name'] = $key;
            $month_element['series'] = $value;
            $multiRevenue['multi'][] = $month_element;
        }

        // Push license=>totalRevenue in single array
        foreach($singleVector as $key=>$value) {
            $total_element = array();
            $total_element['name'] = $key;
            $total_element['value'] = $value;
            $multiRevenue['single'][] = $total_element;
        }

        return $multiRevenue;
    }

}