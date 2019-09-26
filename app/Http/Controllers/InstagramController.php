<?php

namespace App\Http\Controllers;

use App\Services\InstagramService;
use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class InstagramController extends ApiController
{
    private $instagramService;

    public function __construct(InstagramService $instagramService)
    {
        $this->instagramService = $instagramService;
    }


    public function getLikesCount(Request $request)
    {
        $likesCount = $this->instagramService->getLikesCount();
        \Log::info("==== getLocationDetails->details ", ['u' => json_encode($likesCount)]);
        $response = response()->json(['data' => $likesCount], 200);
        \Log::info("==== getLocationDetails->response ", ['u' => json_encode($response)]);
        return $response;
    }

}
