<?php

namespace App\Services;

use App\Repositories\PatientSaturationRepository;
use Join;

class PatientSaturationService
{
    private $patientSaturationRepository;

    /**
     * PatientSaturationService constructor.
     * @param $patientSaturationRepository
     */
    public function __construct(PatientSaturationRepository $patientSaturationRepository)
    {
        $this->patientSaturationRepository = $patientSaturationRepository;
    }

    /**
     * Returns the chart data
     * @return mixed
     */
    public function getCharts()
    {
        $charts = $this->patientSaturationRepository->getCharts();
        return $charts;
    }
}


