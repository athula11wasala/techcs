<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use App\Services\MapService;
use Illuminate\Http\Request;

class TaxRatesController extends ApiController
{

    private $mapService;

    public function __construct(CmsService $chartsService, MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    /**
     * Get tex rates by State.
     * @param CO ,WA,OR,CA
     *
     */

    public function getTaxRatesDetails()
    {


        $taxRatesDetails = $this->mapService->getTaxRatesDetails ();
        if ( $taxRatesDetails ) {
            $taxRatesList = [];
            $taxRatesGlossaryList = [];
            foreach ( $taxRatesDetails as $taxRates ) {

                if ( $taxRates->medical_state == "" ) {

                    $medical_state = "NULL";
                } else {

                    // $medical_state = number_format ( str_replace ( "%", "", $taxRates->medical_state ), 2 ) . '%';
                    $medical_state = $taxRates->medical_state;

                }
                if ( $taxRates->standard_state == "" ) {
                    $standard_state = "NULL";
                } else {
                    $standard_state = $taxRates->standard_state;
                }
                if ( $taxRates->adult_state == "" ) {
                    $adult_state = "NULL";
                } else {
                    $adult_state = $taxRates->adult_state;
                }


                $taxRatesList[ $taxRates->state ][ 'medicalState' ] = $medical_state;
                $taxRatesList[ $taxRates->state ][ 'standardState' ] = $standard_state;
                $taxRatesList[ $taxRates->state ][ 'adultState' ] = $adult_state;
            }

            $taxRatesGlossary = $this->mapService->getTaxRatesGlossary ();

            $i = 0;
            foreach ( $taxRatesGlossary as $glossary ) {
                $taxRatesGlossaryList[ $i ][ 'state' ] = $glossary->state;
                $taxRatesGlossaryList[ $i ][ 'glossaryId' ] = $glossary->glossary_id;
                $taxRatesGlossaryList[ $i ][ 'glossary' ] = $glossary->glossary;
                $i++;
            }
            return response ()->json (
                [
                    'taxRatesList' => $taxRatesList,
                    'taxRatesGlossaryList' => $taxRatesGlossaryList
                ]
                , 200
            );

        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );
    }

    /**
     * Get tex county by State.
     * @param State (CO,WA,OR,CA)
     *
     */

    public function getCountyDetails(Request $request)
    {


        $state = $request->state;
        $countyDetails = $this->mapService->getCountyDetails ( $state );

        if ( $countyDetails ) {
            $countyList = [];
            $taxGlossaryList = [];
            $i = 0;
            foreach ( $countyDetails as $county ) {

                if ( $county->standard_county == "" ) {
                    $standard_county = NULL;
                } else {
                        $standard_county = substr($county->standard_county, 0, strpos($county->standard_county, '%'));
                        $standard_county = (float)filter_var(
                            $standard_county,
                            FILTER_SANITIZE_NUMBER_FLOAT,
                            FILTER_FLAG_ALLOW_FRACTION
                        );

//                    $standard_county = !empty( $county->standard_county ) ? $county->standard_county : 0;


                }
                if ( $county->medical_county == "" ) {
                    $medical_county = NULL;
                } else {
                    $medical_county = substr($county->medical_county, 0, strpos($county->medical_county, '%'));
                     $medical_county = (float)filter_var(
                         $medical_county,
                         FILTER_SANITIZE_NUMBER_FLOAT,
                         FILTER_FLAG_ALLOW_FRACTION
                     );

//                    $medical_county = !empty( $county->medical_county ) ? $county->medical_county : 0;

                }
                if ( $county->adult_county == "" ) {
                    $adult_county = NULL;
                } else {
                      $adult_county = substr($county->adult_county, 0, strpos($county->adult_county, '%'));
                      $adult_county = (float)filter_var(
                          $adult_county,
                          FILTER_SANITIZE_NUMBER_FLOAT,
                          FILTER_FLAG_ALLOW_FRACTION
                      );
//                    $adult_county = !empty( $county->adult_county ) ? $county->adult_county : 0;

                }

                $average = 0;
                try {
                    $average = ($standard_county + $medical_county + $adult_county);
                } catch (\Exception $e) {
                    \Log::error ( 'standard_county: ' . $standard_county );
                    \Log::error ( 'medical_county: ' . $medical_county );
                    \Log::error ( 'adult_county: ' . $adult_county );
                    \Log::error ( 'Error occurred while finding avg:' . $e->getMessage () );
                }

                $average = round ( $average, 2 ) . "%";



                $countyList[ $i ][ 'county' ] = $county->county;
                $countyList[ $i ][ 'standardCounty' ] = (float) str_replace("%","", $standard_county) ;
                $countyList[ $i ][ 'medicalCounty' ] = $medical_county;
                $countyList[ $i ][ 'adultCounty' ] = $adult_county;
                $countyList[ $i ][ 'average' ] = (float) str_replace("%","", $average) ;
                $countyList[ $i ][ 'state' ] = $county->state;


                $i++;
            }

            $taxGlossary = $this->mapService->getTaxGlossary ( $state );
            $x = 0;
            foreach ( $taxGlossary as $taxGlossary ) {

                $taxGlossaryList[ $x ][ 'glossary' ] = $taxGlossary->glossary;
                $taxGlossaryList[ $x ][ 'definition' ] = $taxGlossary->definition;
                $taxGlossaryList[ $x ][ 'state' ] = $taxGlossary->state;
                $x++;
            }

            return response ()->json ( ['countyList' => $countyList, 'taxGlossaryList' => $taxGlossaryList], 200 );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

    public function getCityDetails(Request $request)
    {

        $state = $request->state;
        if ( isset( $request->county ) ) {
            $county = $request->county;
        } else {
            $countyDetails = $this->mapService->getCounty ( $state );
            $county = $countyDetails->county;
        }


        $cityDetails = $this->mapService->getCityDetails ( $state, $county );

        if ( $cityDetails ) {
            $cityList = [];
            $stateLevel = [];
            $countyLevel = [];
            $i = 0;

            $stateLevelDetails = $this->mapService->stateLevelDetails ( $state );
            $stateLevel[ 'standardState' ] = $stateLevelDetails->standard_state;
            $stateLevel[ 'adultState' ] = $stateLevelDetails->adult_state;
            $stateLevel[ 'medicalState' ] = $stateLevelDetails->medical_state;

            $countyLevelDetails = $this->mapService->countyLevelDetails ( $state, $county );
            $countyLevel[ 'standardCounty' ] = $countyLevelDetails->standard_county;
            $countyLevel[ 'adultCounty' ] = $countyLevelDetails->adult_county;
            $countyLevel[ 'medicalCounty' ] = $countyLevelDetails->medical_county;


            foreach ( $cityDetails as $city ) {

                if ( $city->standard_city == "" ) {
                    $standard_city = "NULL";
                } else {
                    $standard_city = $city->standard_city;
                }
                if ( $city->adult_city == "" ) {
                    $adult_city = "NULL";
                } else {
                    $adult_city = $city->adult_city;
                }
                if ( $city->medical_city == "" ) {
                    $medical_city = "NULL";
                } else {
                    $medical_city = $city->medical_city;
                }

                $cityList[ $i ][ 'city' ] = str_replace ( "No Physical Location", "*", $city->city );
                $cityList[ $i ][ 'npl' ] = (strpos ( $city->city, 'No Physical Location' )) ? 1 : 0;
                $cityList[ $i ][ 'standardCity' ] = $standard_city;
                $cityList[ $i ][ 'adultCity' ] = $adult_city;
                $cityList[ $i ][ 'medicalCity' ] = $medical_city;
                $i++;
            }

            return response ()->json (
                [
                    'cityLevel' => $cityList,
                    'stateLevel' => $stateLevel,
                    'countyLevel' => $countyLevel
                ],
                200
            );
        }
        return response ()->json ( ['error' => __ ( 'messages.un_processable_request' )], 400 );

    }

}
