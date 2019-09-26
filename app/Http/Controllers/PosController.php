<?php

namespace App\Http\Controllers;

use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class PosController extends ApiController
{
    private $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }


    public function getLocationDetails(Request $request)
    {
        $email = $request->email;
        $details = $this->posService->getLocationDetails($email);
        $response = response()->json(['data' => $details], 200);
        return $response;
    }

    public function getBestProductPrices(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $start = $request->start;
        $end = $request->end;
        $productPrices = $this->posService->getProductPrices($licenses, $start, $end);
        $response = response()->json(['data' => $productPrices], 200);
        return $response;
    }

    public function getWorstProductPrices(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $start = $request->start;
        $end = $request->end;
        $productPrices = $this->posService->getProductPrices($licenses, $start, $end, "ASC");
        $response = response()->json(['data' => $productPrices], 200);
        return $response;
    }

    public function getMonthlyRevenue(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $start = $request->start;
        $end = $request->end;
        $monthlyRevenue = $this->posService->getMonthlyRevenue($licenses, $start, $end);
        $response = response()->json(['data' => $monthlyRevenue], 200);
        return $response;
    }

    public function getExpenses(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $start = $request->start;
        $end = $request->end;
        $vendors = $this->posService->getExpenses($licenses, $start, $end);
        $response = response()->json($vendors, 200);
        return $response;
    }

    public function getTotalSalesExpenses(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $start = $request->start;
        $end = $request->end;
        $salesExpenses = $this->posService->getTotalSalesExpenses($licenses, $start, $end);
        $response = response()->json($salesExpenses, 200);
        return $response;
    }

    public function getVendors(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $vendors = $this->posService->getVendors($licenses);
        $response = response()->json($vendors, 200);
        return $response;
    }

    public function addVendors(Request $request)
    {
        $licenses = str_replace('%2B','+',
            str_replace('%2F','/',$request->licenses));
        $company_name = $request->company_name;
        $category = $request->category;
        $account = $request->account;
        $title = $request->title;
        $first_name = $request->first_name;
        $middle_name = $request->middle_name;
        $last_name = $request->last_name;
        $address = $request->address;
        $zipcode = $request->zipcode;
        $city = $request->city;
        $state = $request->state;
        $email = $request->email;
        $phone = $request->phone;
        $website  = $request->website;
        $hour_billing = $request->hour_billing;
        $opening_balance = $request->opening_balance;
        $date_balance = $request->date_balance;
        $internet_rating = $request->internet_rating;
        $vendors = $this->posService->addVendors(
            $licenses, $company_name, $category, $account, $title, $first_name, $middle_name, $last_name,
            $address, $zipcode, $city, $state, $email, $phone, $website,
            $hour_billing, $opening_balance, $date_balance, $internet_rating);
        $response = response()->json($vendors, 200);
        return $response;
    }

    public function getSettings(Request $request)
    {
        \Log::info("==== PosController->getSettings ", ['u' => json_encode($request)]);
        $email = $request->email;
        $settings = $this->posService->getSettings($email);
        \Log::info("==== PosController->getSettings ", ['u' => json_encode($settings)]);
        $response = response()->json($settings, 200);
        return $response;
    }

    public function updateSettings(Request $request)
    {
        $email = $request->email;
        $quickbooks_clientid = $request->quickbooks_clientid;
        $quickbooks_secret = $request->quickbooks_secret;
        $tweeter_name = $request->tweeter_name;
        $facebook_userid = $request->facebook_userid;
        $instagram_userid = $request->instagram_userid;
        $instagram_clientid = $request->instagram_clientid;
        $instagram_secret = $request->instagram_secret;
        $pos_name = $request->pos_name;
        $pos_apikey = $request->pos_apikey;
        $pos_username = $request->pos_username;
        $pos_password = $request->pos_password;
        $vendors = $this->posService->updateSettings($email, $quickbooks_clientid, $quickbooks_secret, $tweeter_name,
            $facebook_userid, $instagram_userid, $instagram_clientid, $instagram_secret,
            $pos_name, $pos_apikey, $pos_username, $pos_password);
        $response = response()->json($vendors, 200);
        return $response;
    }

}
