<?php

namespace App\Http\Controllers;

use App\Services\CompanyProfilesService;
use App\Services\SearchService;
use App\Traits\CompanyProfileValidators;
use Illuminate\Http\Request;
use App\Equio\Helper;
use Illuminate\Support\Facades\Config;


class CompanyController extends ApiController
{

    use CompanyProfileValidators;

    private $error = 'error';
    private $message = 'message';

    /**
     * @var UserService
     */
    private $companyProfileService;


    /**
     * UsersController constructor.
     * @param UserService $userService
     */
    public function __construct(CompanyProfilesService $companyProfileService)
    {
        $this->companyProfileService = $companyProfileService;

    }


    public function index(Request $request)
    {

        $companyData = $this->companyProfileService->getAllComapny($request->all());

        return $this->respond($companyData);
    }

    public function showCompany($id = null)
    {
        $companyData = $this->companyProfileService->getComapnyById($id);

        return $this->respond($companyData);

    }

    public function searchCompany(Request $request)
    {
        $companyData = $this->companyProfileService->getAllComapnyDetail($request);

        if ($companyData) {

            return response()->json(['data' => $companyData], 200);

        }
        return response()->json([$this->error => __('messages.un_processable_request')], 400);

    }

/////////////////////
    public function addNewCompany(Request $request)
    {

        $validator = $this->companyProfileValidate($request->all());

        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);

        }

        if ($validator->passes()) {

            $companyData = $this->companyProfileService->createCompany($request);

            if ($companyData) {
                return response()->json(['message' => __('messages.company_profile_add_success')], 200);
            }

            return response()->json(['error' => __('messages.un_processable_request')], 400);

        }

    }

    public function editCompany(Request $request)
    {
        $validator = $this->companyProfileValidate($request->all(), 'PUT');

        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);

        }

        if ($validator->passes()) {
            $companyData = $this->companyProfileService->getUpdateCompany($request);
            if ($companyData) {
                return response()->json(['message' => __('messages.company_profile_edit_success')], 200);
            }

            return response()->json(['error' => __('messages.un_processable_request')], 400);

        }

    }

    public function deleteCompany($id = null)
    {

        $validator = $this->companyProfileValidate(['id' => $id], 'DELETE');

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($validator->passes()) {
            $companyData = $this->companyProfileService->deleteCompany(['id' => $id]);

            if ($companyData) {
                return response()->json(['message' => __('messages.company_profile_delete_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);

        }

    }

    public function getProfileContentData(Request $request)
    {
        $profileId = $request->id;
        $pathToFile = null;
        $profileData = $this->companyProfileService->getComapnyById($profileId);

        $pathToFile = $this->urlEncoderWithOutBase(
            !empty($profileData->full_pdf) ? $profileData->full_pdf : ''
        );
        $headers = array(
            'Content-Description: File Transfer',
            'Content-Type: application/octet-stream',
            'Content-Disposition: attachment; filename="' . $profileData->name . '"',
        );
        return response()->download("$pathToFile", $profileData->name, $headers);
    }


    private function urlEncoderWithOutBase($url)
    {
        $fullUrl = Config::get('custom_config.company_profile_document') . $url;
        return $fullUrl;
    }


}

