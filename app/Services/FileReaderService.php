<?php

namespace App\Services;

use App\DasetFactory\DatasetFactoryMethod;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\DataSetRepository;
use DB;
use Excel;
use Join;


class FileReaderService
{

    private $dataSetRepository;

    public function __construct(DataSetRepository $dataSetRepository)

    {

        $this->dataSetRepository = $dataSetRepository;

    }


    public function writeExcelToDb($dasetId, $readCsv, $tblName, $type)
    {
        $objDatasetFac = new  DatasetFactoryMethod();
        $path = base_path ( "public/" . $readCsv[ 'fileName' ] );
        $tableColumn = DB::select (
            DB::raw ( 'show columns from ' . $tblName )
        );
        unset( $tableColumn[ 0 ] );
        $tableColumn = array_values ( $tableColumn );

        $lastElement = array_values ( array_slice ( $tableColumn, -1 ) )[ 0 ];

        if ( $lastElement->Field == "updated_at" ) {

            array_pop ( $tableColumn );
            $lastElement = array_values ( array_slice ( $tableColumn, -1 ) )[ 0 ];
        }
        if ( $lastElement->Field == "created_at" ) {

            array_pop ( $tableColumn );
            $lastElement = array_values ( array_slice ( $tableColumn, -1 ) )[ 0 ];
        }
        if ( $lastElement->Field == "dataset_id" ) {

            array_pop ( $tableColumn );

        }


        $data = Excel::load ( $path, function ($reader) {
        } )->get ();

        $HeaderColumn = $data->first ()->keys ()->toArray ();
        $datas = [];

        if ( count ( $tableColumn ) != count ( $HeaderColumn ) ) {

            //\File::delete(public_path($path));
            return ['msg' => 'There were uploded wrong csv'];
        }

        $insert = [];

        if ( !empty( $data ) && $data->count () ) {

            foreach ( $data as $rows ) {

                $tags = array_map ( function ($rows) use ($objDatasetFac) {

                    return $objDatasetFac->makeDatasetRead ( $rows, 2 );

                }, (array)$rows );

                $key = array_keys ( $tags );
                $insert[] = $tags[ $key[ 1 ] ];
            }

            if ( !empty( $insert ) ) {

                DB::table ( $tblName )->insert ( $insert );

                return true;
            }

        }


    }

    public function addUploadCsvDataSet($request)
    {

        $dataCsv = $request[ 'datacsv' ];
        $type = $request[ 'type' ];
        unset( $request[ 'datacsv' ] );
        $dataSetId = $this->dataSetRepository->saveDataSet ( $request );
        $uploadCsv_table = $this->dataSetRepository->uploadCsvDataSet ( $dataCsv, $request[ 'type' ] );
        $tblName = '';

        if ( empty( $uploadCsv_table ) ) {

            return ['msg' => 'There is already uploded this csv'];

        } else {

            $tableName = $uploadCsv_table[ 'tblName' ];

            return $this->writeExcelToDb ( $dataSetId, $uploadCsv_table, $tableName, $type );
        }

    }


}

