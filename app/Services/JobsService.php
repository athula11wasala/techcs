<?php

namespace App\Services;

use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\JobsRepository;
use App\Repositories\QualifyConditionRepository;
use Join;

class JobsService
{

    private $jobsRepository;
    private $qualifyConditionRepository;

    /**
     * JobsService constructor.
     * @param $jobsRepository
     */
    public function __construct(JobsRepository $jobsRepository, QualifyConditionRepository $qualifyConditionRepository)
    {
        $this->jobsRepository = $jobsRepository;
        $this->qualifyConditionRepository = $qualifyConditionRepository;
    }

    public function getAllJob($request)
    {
        return $this->jobsRepository->allJobInfo($request);

    }

    public function getMapInfo($request)
    {
        return $this->jobsRepository->allMapInfo($request);

    }

    public function getMapDetailByState($request)
    {
        return $this->jobsRepository->allMapInfoByState($request);

    }
}


