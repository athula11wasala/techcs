<?php

namespace App\Services;

use App\Repositories\PosExpensesRepository;
use App\Repositories\PosLocationsRepository;
use App\Repositories\PosSalesTransactionsRepository;
use App\Repositories\PosSettingsRepository;
use App\Repositories\PosUsersLocationsRepository;
use App\Repositories\PosVendorsRepository;
use Join;

class PosService
{

    private $posSalesTransactionsRepository;
    private $posUsersLocationsRepository;
    private $posLocationsRepository;
    private $posVendorsRepository;
    private $posExpensesRepository;
    private $posSettingsRepository;

    /**
     * PosService constructor.
     * @param $posRepository
     */
    public function __construct(PosUsersLocationsRepository $posUsersLocationsRepository,
                                PosLocationsRepository $posLocationsRepository,
                                PosSalesTransactionsRepository $posSalesTransactionsRepository,
                                PosVendorsRepository $posVendorsRepository,
                                PosExpensesRepository $posExpensesRepository,
                                PosSettingsRepository $posSettingsRepository)
    {
        $this->posUsersLocationsRepository = $posUsersLocationsRepository;
        $this->posLocationsRepository = $posLocationsRepository;
        $this->posSalesTransactionsRepository = $posSalesTransactionsRepository;
        $this->posVendorsRepository = $posVendorsRepository;
        $this->posExpensesRepository = $posExpensesRepository;
        $this->posSettingsRepository = $posSettingsRepository;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getLocationDetails($email)
    {
        $locationDetails = array();
        $licences = $this->posUsersLocationsRepository->getLicenses($email);
        foreach($licences as $licence) {
            $locationDetails[] = $this->posLocationsRepository->getLocationDetail($licence["LicenseNumber"]);
        }
        return $locationDetails;
    }

    public function getProductPrices($licences, $start="", $end="", $order="DESC")
    {
        return $this->posSalesTransactionsRepository->getProductPrices($licences, $start, $end, $order);
    }

    public function getMonthlyRevenue($licences, $start="", $end="")
    {
        return $this->posSalesTransactionsRepository->getMonthlyRevenue($licences, $start, $end);
    }

    public function getExpenses($licences, $start="", $end="")
    {
        return $this->posExpensesRepository->getExpenses($licences, $start, $end);
    }

    public function getTotalSalesExpenses($licences, $start="", $end="")
    {
        $totalSalesExpenses = array(
            'revenue'=>$this->posSalesTransactionsRepository->getTotalSales($licences, $start, $end),
            'expenses'=>$this->posExpensesRepository->getTotalExpenses($licences, $start, $end)
        );
        return $totalSalesExpenses;
    }

    public function getVendors($licences)
    {
        return $this->posVendorsRepository->getVendors($licences);
    }

    public function addVendors(
        $licenses, $company_name, $category, $account, $title, $first_name, $middle_name, $last_name,
        $address, $zipcode, $city, $state, $email, $phone, $website,
        $hour_billing, $opening_balance, $date_balance, $internet_rating)
    {
        \Log::info("==== PosService->addVendors ");
        return $this->posVendorsRepository->addVendors(
            $licenses, $company_name, $category, $account, $title, $first_name, $middle_name, $last_name,
            $address, $zipcode, $city, $state, $email, $phone, $website,
            $hour_billing, $opening_balance, $date_balance, $internet_rating);
    }

    public function getSettings($email)
    {
        \Log::info("==== PosService->getSettings ", ['u' => json_encode($email)]);
        return $this->posSettingsRepository->getSettings($email);
    }

    public function updateSettings(
        $email, $quickbooks_clientid, $quickbooks_secret, $tweeter_name,
        $facebook_userid, $instagram_userid, $instagram_clientid, $instagram_secret,
        $pos_name, $pos_apikey, $pos_username, $pos_password)
    {
        \Log::info("==== PosService->updateSettings ");
        return $this->posSettingsRepository->updateSettings(
            $email, $quickbooks_clientid, $quickbooks_secret, $tweeter_name,
            $facebook_userid, $instagram_userid, $instagram_clientid, $instagram_secret,
            $pos_name, $pos_apikey, $pos_username, $pos_password);
    }

}


