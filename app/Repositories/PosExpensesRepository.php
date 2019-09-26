<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosExpensesRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosExpenses';
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getExpenses($licenses, $start = "", $end = "", $order = "DESC")
    {
        $licenses = str_replace(",", "','", $licenses);
        $date_between = (
        (empty($start) || empty($end)) ? "" :
            "AND TotalPrice between date('$start') and date('$end')"
        );
        $result = DB::connection('mysql_external_intake')->select(
            DB::raw("
                SELECT SUM(TotalPrice) as value, VendorCategory as name
                FROM pos_expenses
                WHERE LicenseNumber IN ('$licenses')
                $date_between
                GROUP BY VendorCategory;"
            )
        );
        return $result;
    }


    /**
     * Returns the total expenses for the licenses
     * @param $licenses
     * @return mixed
     */
    public function getTotalExpenses($licenses, $start = "", $end = "")
    {
        $licenses = str_replace(",", "','", $licenses);
        $date_between = ((empty($start) || empty($end))
            ? ""
            : "AND TotalPrice between date('$start') and date('$end')");
        $result = DB::connection('mysql_external_intake')->select(DB::raw("
            SELECT SUM(pos_expenses.TotalPrice) as expenses
            FROM `pos_expenses`
            WHERE pos_expenses.LicenseNumber in ('$licenses')
            $date_between;
        "));
        \Log::info("==== PosExpensesRepository->getTotalExpenses ", ['u' => json_encode($result)]);
        return $result;
    }

}