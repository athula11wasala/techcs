<?php

namespace App\Http\Controllers;

use App\Services\PatientSaturationService;
use Illuminate\Http\Request;

class PatientSaturationController extends ApiController
{
    public function __construct(PatientSaturationService $patientSaturationService)
    {
        $this->patientSaturationService = $patientSaturationService;
    }

    public function getCharts()
    {
        $charts = $this->patientSaturationService->getCharts();
        $response = response()->json(['charts' => $charts], 200);
        return $response;
    }
}
