<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use DB;


class PosVendorsRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\PosVendors';
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function getVendors($licenses, $order="DESC")
    {
        $licenses = str_replace(",","','",$licenses);
        $result = DB::connection('mysql_external_intake')->select( DB::raw("
                SELECT pos_vendors.VendorCompanyName, pos_vendors.VendorAddress, pos_vendors.VendorZip
                FROM pos_vendors
                WHERE pos_vendors.VendorLicenseNumber IN ('$licenses')
                GROUP BY pos_vendors.VendorCompanyName, pos_vendors.VendorAddress, pos_vendors.VendorZip;
        ") );
        return $result;
    }

    /**
     * Returns list of best/worst product list and prices for each locations/licences with national average
     * @param $licenses
     * @return mixed
     */
    public function addVendors($licenses, $company_name, $category, $account, $title, $first_name, $middle_name, $last_name,
                               $address, $zipcode, $city, $state, $email, $phone, $website,
                               $hour_billing, $opening_balance, $date_balance, $internet_rating)
    {
        $licenses = explode(",", str_replace("'","", $licenses));
        $hour_billing = !empty($hour_billing) ? "$hour_billing" : "NULL";
        $opening_balance = !empty($opening_balance) ? "$opening_balance" : "NULL";
        $zipcode = !empty($zipcode) ? "$zipcode" : "NULL";
        $internet_rating = !empty($internet_rating) ? "$internet_rating" : "NULL";
        $phone = !empty($phone) ? "$phone" : "NULL";
        $date_balance = !empty($date_balance) ? "'$date_balance'" : "NULL";
        $result = array();
        $address = addslashes($address);
        $company_name = addslashes($company_name);
        $account = addslashes($account);
        $first_name = addslashes($first_name);
        $last_name = addslashes($last_name);
        foreach ($licenses as $license) {
            $result[] = DB::connection('mysql_external_intake')->select(DB::raw("
                  INSERT INTO pos_vendors 
                  (
                     `VendorLicenseNumber`, `VendorCompanyName`, `VendorCategory`, `VendorAccountNumber`,
                     `VendorPersonTitle`, `VendorPersonFirstName`, `VendorPersonMiddleName`, `VendorPersonLastName`,
                     `VendorAddress`, `VendorZip`, `VendorCity`, `VendorState`, `VendorEmail`, `VendorPhone`,
                     `VendorWebsite`, `VendorHourBilling`, `VendorOpeningBalance`, `VendorDateBalance`, `VendorInternetRating`
                  ) VALUES (
                     '$license', '$company_name', '$category', '$account',
                     '$title', '$first_name', '$middle_name', '$last_name',
                     '$address', $zipcode, '$city', '$state', '$email', $phone,
                     '$website', $hour_billing, $opening_balance, $date_balance, $internet_rating
                  );
            "));
        }
        \Log::info("==== PosVendorsRepository->addVendors ", ['u' => json_encode($result)]);
        return $result;
    }

}