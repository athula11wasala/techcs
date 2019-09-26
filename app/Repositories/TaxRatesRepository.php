<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use App\Equio\Helper;
use App\Models\DataSet;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TaxRatesRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */

    public function model()
    {

        return 'App\Models\TaxRates';
    }


    /**
     * Returns tax rates
     */
    public function getTaxRates()
    {

        $allCountry = $taxRates = $this->model
            ->select ( 'county' )
            ->groupby ( 'county' )
            ->get ();

        $taxRateArr = [];

        foreach ( $allCountry as $rows ) {

            $result = $this->model
                ->select ( 'id' )
                ->where ( 'county', $rows->county )
                ->orderBy ( "created_at", "desc" )
                ->first ();
            $taxRateArr[] = $result->id;

        }

        $taxRates = $this->model
            ->select ( 'standard_state', 'adult_state', 'state', 'medical_state', 'latest' )
            ->where ( function ($query) {
                $query->orWhere ( 'state', 'CO' )
                    ->orWhere ( 'state', 'WA' )
                    ->orWhere ( 'state', 'OR' )
                    ->orWhere ( 'state', 'CA' );
            } )
            ->whereIn ( 'id', $taxRateArr )
            ->orderBy ( "created_at", "desc" )
            ->get ();

        return $taxRates;

    }

    public function getCountyInfo($state)
    {


        $cityList = $this->model
            ->select ( 'county' )
            ->where ( 'state', $state )
            ->groupBy ( "county" )
            ->get ();


        $taxRateArr = [];

        foreach ( $cityList as $rows ) {

            $result = $this->model
                ->select ( 'id', 'county' )
                ->where ( 'county', $rows->county )
                ->where ( 'state', $state )
                ->orderBy ( "created_at", "desc" )
                ->first ();

            if ( $result->county == "Spokane" ) {


            }
            $taxRateArr[] = $result->id;


        }


        $taxRates = $this->model
            ->select ( 'id', 'county', 'standard_county', 'medical_county', 'state', 'adult_county' )
            ->whereIn ( 'id', $taxRateArr )
            ->orderBy ( "county", "asc" )
            ->get ();


        return $taxRates;


    }

    public function getCounty($state)
    {

        $county = $this->model
            ->select ( 'id', 'county' )
            ->where ( 'state', $state )
            ->orderBy ( "created_at", "desc" )
            // ->distinct ()
            ->first ();

        return $county;

    }

    public function getCity($state, $county)
    {

        $cityList = $this->model
            ->select ( 'id', 'city', 'standard_city', 'adult_city', 'medical_city', 'state' )
            ->where ( 'state', $state )
            ->where ( 'county', $county )
            ->orderBy ( "created_at", "desc" )
            // ->distinct ()
            ->get ();

        $data[ 'city' ] = [];
        $idArr = [];

        foreach ( $cityList as $rows ) {

            if ( !isset( $data[ 'city' ][ $rows->city ] ) ) {
                $data[ 'city' ][ $rows->city ] = 'city';
                $idArr [] = $rows->id;
            }

        }


        $cityList = $this->model
            ->select ( 'id', 'city', 'standard_city', 'adult_city', 'medical_city', 'state' )
            ->where ( 'state', $state )
            ->where ( 'county', $county )
            ->orderBy ( "created_at", "desc" )
            ->whereIn ( "id", $idArr )
            ->get ();

        return $cityList;

    }

    public function stateLevel($state)
    {

        $stateLevel = $this->model
            ->select ( 'id', 'standard_state', 'adult_state', 'state', 'medical_state' )
            ->where ( 'state', $state )
            //->distinct ()
            ->orderBy ( "created_at", "desc" )
            ->first ();


        return $stateLevel;

    }

    public function countyLevel($state, $county)
    {
        $cityList = $this->model
            ->select ( 'county', 'standard_county', 'medical_county', 'state', 'adult_county' )
            ->where ( 'state', $state )
            ->where ( 'county', $county )
            //  ->distinct ()
            ->orderBy ( "created_at", "desc" )
            ->first ();

        return $cityList;

    }


}