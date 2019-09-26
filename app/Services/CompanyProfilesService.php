<?php

namespace App\Services;

use App\Repositories\CompanyProfilesRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\UserRepository;
use Join;

class CompanyProfilesService
{

    private $companyProfileRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(CompanyProfilesRepository $compnayProfileRepository)
    {
        $this->companyProfileRepository = $compnayProfileRepository;
    }

    public function createCompany($companyArray)
    {
        return $this->companyProfileRepository->saveCompany($companyArray);
    }

    public function getUpdateCompany($companyArray)
    {
        return $this->companyProfileRepository->updateCompany($companyArray);
    }

    public function getComapnyByName($name)
    {
        return $this->companyProfileRepository->comapnyInfoByName($name);
    }

    public function getComapnyById($id)
    {
        return $this->companyProfileRepository->comapnyInfoById($id);
    }

    public function getAllComapnyDetail($request)
    {
        return $this->companyProfileRepository->allComapnyDetail($request->all ());
    }

    public function getAllComapny($request)
    {
        return $this->companyProfileRepository->allComapnyInfo($request);
    }

    public function deleteCompany($companyId)
    {
        return $this->companyProfileRepository->deleteCompany($companyId);
    }




}


