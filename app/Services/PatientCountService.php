<?php

namespace App\Services;

use App\Repositories\PatientCountRepository;
use Join;

class PatientCountService
{
    private $patientCountRepository;

    /**
     * PatientSaturationService constructor.
     * @param $patientSaturationRepository
     */
    public function __construct(PatientCountRepository $patientCountRepository)
    {
        $this->patientCountRepository = $patientCountRepository;
    }

    /**
     * Returns the chart data
     * @return mixed
     */
    public function getCharts()
    {
        $charts = $this->patientCountRepository->getCharts();
        return $charts;
    }
}


