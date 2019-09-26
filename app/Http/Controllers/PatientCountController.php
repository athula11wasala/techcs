<?php

namespace App\Http\Controllers;

use App\Services\PatientCountService;
use Illuminate\Http\Request;

class PatientCountController extends ApiController
{
    public function __construct(PatientCountService $patientCountService)
    {
        $this->patientCountService = $patientCountService;
    }

    public function getCharts()
    {
        $charts = $this->patientCountService->getCharts();
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }
}
