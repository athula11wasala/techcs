<?php

namespace App\Repositories;

use App\Models\DataSet;
use Bosnadev\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use App\Equio\Helper;
use Illuminate\Support\Facades\DB;

class DataSetRepository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\DataSet';
    }

    public function saveDataSet($data)
    {
        $objDataSet = new DataSet();
        $objDataSet->data_set = $data[ 'type' ];
        $objDataSet->description = $data[ 'quater' ];
        $objDataSet->from = Carbon::parse ( $data[ 'fromDate' ] );
        $objDataSet->to = Carbon::parse ( $data[ 'toDate' ] );

        if ( $objDataSet->save () ) {

            return $objDataSet->id;
        }

    }


    public function uploadCsvDataSet($excelFile, $type = null, $flag = false)
    {

        $tblName = "";


        switch ($type) {

            case 1:

                $destinationPath = Config::get ( 'custom_config.data_set_legal_state' );
                $tblName = 'state_legalized';

                break;
            case 2:

                $destinationPath = Config::get ( 'custom_config.data_set_qualify_condition' );
                $tblName = 'qualifying_conditions';

                break;
            case 3:

                $destinationPath = Config::get ( 'custom_config.data_set_tax_rate' );
                $tblName = 'taxrates';

                break;
            case 4:

                $destinationPath = Config::get ( 'custom_config.data_set_caninbench_markus' );
                $tblName = 'cannabis_benchmarks_us';
                break;
            default:
                break;

        }

        if ( $excelFile ) {

            $image = $excelFile;
            $fileName = $image->getClientOriginalName ();


            if ( !file_exists ( $destinationPath ) ) {
                mkdir ( $destinationPath, 0777, true );
            }

            if ( file_exists ( $destinationPath . "/" . $fileName ) ) {

                return false;
            } else {

                $excelFile->move ( $destinationPath, $fileName );
            }

            return ['flag' => true, 'fileName' => $destinationPath . $fileName, 'tblName' => $tblName];

        }

    }

    /**
     * Returns all Dataset InvestmentRank info
     *
     * @param $request
     * @return mixed
     */
    public function getDataset($request)
    {

        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->perPage = (!empty($request['perPage'])) ? ($request['perPage']) : '15';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'updated_at';
        $this->limit = (!empty($request['limit'])) ? ($request['limit']) : 100;
        $datasetId = Config::get ( 'custom_config.data_set' )[ 'investment_ranking_threshold_us' ];
        $activeRankDataSetId = !empty( $this->getInvestmentRankActiveDataSet()) ?  $this->getInvestmentRankActiveDataSet() : 0;

        $columns = ['*'];
        $or = false;
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        $model = $this->model->select(
            "id","data_set","description","from","to","created_at")
            ->selectRaw ( "$activeRankDataSetId as status" );

        foreach ($request as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, 'like', '%' . $value . '%')
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, 'like', '%' . $value . '%')
                    : $model->orWhere($field, '=', $value);
            }
        }


        $result = $model
            ->where ( "data_set",$datasetId )
            ->orderBy($this->sortColumn, $this->sort)
            ->paginate($this->perPage);

        return $result;
    }

    public function getInvestmentRankActiveDataSet(){

       $result = DB::Connection ( "mysql_external_intake" )->table ( 'investment_ranking_threshold_us' )->select("dataset_id")->where("latest",1)->first();
       if(!empty($result->dataset_id)){

           return $result->dataset_id;
       }
       return  '';
    }


}