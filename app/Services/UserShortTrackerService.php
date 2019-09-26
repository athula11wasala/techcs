<?php

namespace App\Services;

use App\Repositories\UserShortTrackerRepository;

class UserShortTrackerService {

    private $userShortTrackerRepository;

    public function __construct(
        UserShortTrackerRepository $userShortTrackerRepository
    ) {
        $this->userShortTrackerRepository = $userShortTrackerRepository;
    }

    public function createUserShortTracker(
        $userShortTrackersDataArr = array()
    ) {
        return $this->userShortTrackerRepository
            ->createUserShortTracker($userShortTrackersDataArr);
    }


    public function createExistUserShortTracker(
        $userShortTrackersDataArr = array(),$userId = null
    ) {
        return $this->userShortTrackerRepository
            ->addExistCustomerSymbol($userShortTrackersDataArr, $userId);
    }

    public function deleteUserShortTracker(
        $userId = null
    ) {
        return $this->userShortTrackerRepository
            ->deleteUserShortTracker($userId);
    }

    public function getCanceledCompanies(
        $companySymbols = array(),$userId = null
    ) {
        $existingCompanies = $this->userShortTrackerRepository
            ->getExistingCompanies($userId);

        $symbols = [];
        
        foreach ($existingCompanies as $existingCompany ) {
            array_push($symbols, $existingCompany->symbol);
        }

        $removedCompanies = array_diff($symbols, $companySymbols);
        
        return $removedCompanies;
    }

    public function updateBasicCompanies(
        $data = [], $userId = null
    ) {
        return $this->userShortTrackerRepository
            ->updateBasicCompanies($data, $userId);

        
    }



}