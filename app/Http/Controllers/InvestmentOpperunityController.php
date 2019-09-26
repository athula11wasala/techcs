<?php

namespace App\Http\Controllers;

use App\Equio\Exceptions\EquioException;
use App\Equio\Helper;
use App\Services\CmsService;
use App\Services\MapService;
use App\Traits\InvestmentOpperunityValidator;
use Maatwebsite\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


class InvestmentOpperunityController extends ApiController
{

    use InvestmentOpperunityValidator;
    private $cmsService;
    private $mapService;
    private $error;

    public function __construct(CmsService $cmsService,MapService $mapService)

    {
        $this->cmsService = $cmsService;
        $this->mapService = $mapService;
    }

    public function addInvestMentRank(Request $request)
    {
        $validator = $this->investmentValidate($request->all());
        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $data = $this->cmsService->createInvestmentRank($request->all());

            if ($data) {
                if (isset($data['success']) && !empty($data['message'])) {

                    return response()->json(['error' => __($data['message'])], 400);
                }
                return response()->json(['message' => __('messages.investment_rank_add_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }
    }

    public function investMentRankInfo(Request $request)
    {
        $data = $this->mapService->getInvestmentRankDetails (  );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function sortInvestmentRankByCode(Request $request)
    {
        $data = $this->mapService->sortInvestmentRankByCode (  );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getInvestmentDataSet(Request $request)
    {
        $data = $this->mapService->getInvestmentRankDataSet ($request->all()  );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function selectInvestmentRank(Request $request)
    {
        $data = $this->mapService->getInvestmentRankByDataSet ( $request->all()  );

        if ( $data ) {
            if(count($data['rank_data'])>0){
                return response ()->json ( ['data' => $data], 200 );
            }
            return response ()->json ( ['data' => null], 200 );

        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function editInvestMentRank(Request $request)
    {
        $validator = $this->investmentValidate($request->all(),'PUT');
        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $data = $this->cmsService->editInvestmentRank($request->all());

            if ($data) {
                if (isset($data['success']) && !empty($data['message'])) {

                    return response()->json(['error' => __($data['message'])], 400);
                }
                return response()->json(['message' => __('messages.investment_rank_edit_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }
    }

    public function getAllInvestMentRank(Request $request)
    {
        $data = $this->mapService->getAllInvestmentRankDetails ($request->all()  );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getAllThreshold(Request $request)
    {
        $data = $this->mapService->getAllThresholdDetails ( $request->all() );

        if ( $data ) {

            return response ()->json ( ['data' => $data], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }


    public function editThreshold(Request $request)
    {
        $validator = $this->investmentValidate($request->all(),"chkThreshold");
        if ($validator->fails()) {

            $validateMessge = Helper::customErrorMsg($validator->messages());

            return response()->json(['error' => $validateMessge], 400);
        }
        if ($validator->passes()) {
            $data = $this->cmsService->editThreshold($request->all());

            if (!empty($data)) {

                return response()->json(['message' => __('messages.threshold_edit_success')], 200);
            }
            return response()->json(['error' => __('messages.un_processable_request')], 400);
        }
    }



}









