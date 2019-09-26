<?php

namespace App\Http\Controllers;

use App\Services\FileReaderService;
use App\Traits\CsvDatasetValidators;
use Illuminate\Http\Request;


class UploadController extends ApiController
{
    use CsvDatasetValidators;

    private $fileReaderService;


    public function __construct(FileReaderService $fileReaderService)
    {
        $this->fileReaderService = $fileReaderService;

    }

    public function uploadCsv(Request $request)
    {

        $validator = $this->dataSetValidate ( $request->all () );

        if ( $validator->fails () ) {

            return response ()->json ( ['error' => $validator->errors ()], 400 );
        }

        if ( $validator->passes () ) {

            $dataSetData = $this->fileReaderService->addUploadCsvDataSet ( $request->all () );

            if ( $dataSetData ) {

                if ( $dataSetData[ 'msg' ] ) {

                    return response ()->json ( ['message' => __ ( $dataSetData[ 'msg' ] )], 200 );
                }
                return response ()->json ( ['message' => __ ( 'Successfully saved' )], 200 );
            }

            return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

        }
    }


}