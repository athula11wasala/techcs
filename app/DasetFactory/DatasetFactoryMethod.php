<?php

namespace App\DasetFactory;


abstract class AbstractFactoryMethod
{
    abstract function makeDatasetRead($rows, $param);
}

class DatasetFactoryMethod extends AbstractFactoryMethod
{

    public function makeDatasetRead($rows, $param = [])
    {
        $dataArray = NULL;
        switch ($param[ 'id' ]) {
            case "1":
///latest
                $dataArray = $this->chart ( $rows, $param[ 'reportId' ] !== null ? strval($param[ 'reportId' ]) : 0 );
                break;
            case "2":
                $dataArray = $this->investmentThreshold ( $rows,  $param[ 'dataset_id' ] !== null ? strval($param[ 'dataset_id' ]) : 0,
                 !is_null( $param[ 'latest' ] ) ? $param[ 'latest' ] : 0
                );
                break;
            case "3":
                $dataArray = $this->investmentRankState ( $rows, $param[ 'dataset_id' ] !== null ? strval($param[ 'dataset_id' ]) : 0,
                    !is_null( $param[ 'latest' ] ) ? $param[ 'latest' ] : 0
                    );
                break;
            case "4":
                //    $dataArray = $this->cannabisBenchmarksUs($rows,2);
                break;
            case "5":
                //    $dataArray = $this->cannabisBenchmarksUs($rows,2);
                break;
            default:
                break;
        }
        return $dataArray;
    }

    public function qaulifyCondtion($rows, $dataset)
    {
        return array(
            'state' => (!empty( $rows[ 'state' ] )) ? ($rows[ 'state' ]) : '',
            'alzheimer' => (!empty( $rows[ 'alzheimers_disease' ] )) ? ($rows[ 'alzheimers_disease' ]) : '',
            'lou_gehrig' => (!empty( $rows[ 'amyotrophic_lateral_sclerosis_lou_gehrigs_disease' ] )) ?
                ($rows[ 'amyotrophic_lateral_sclerosis_lou_gehrigs_disease' ]) : '',
            'anorexia' => (!empty( $rows[ 'anorexia' ] )) ? ($rows[ 'anorexia' ]) : '',
            'debilitating' => (!empty( $rows[ 'any_debilitating_illness_deemed_appropriate_by_a_physician' ] )) ?
                ($rows[ 'any_debilitating_illness_deemed_appropriate_by_a_physician' ]) : '',
            'arnold_chiari' => (!empty( $rows[ 'arnold_chiari_malformation' ] )) ?
                ($rows[ 'arnold_chiari_malformation' ]) : '',
            'arthritis' => (!empty( $rows[ 'arthritis' ] )) ? ($rows[ 'arthritis' ]) : '',
            'cachexia_wasting' => (!empty( $rows[ 'cachexiawasting_syndrome' ] )) ?
                ($rows[ 'cachexiawasting_syndrome' ]) : '',
            'cancer' => (!empty( $rows[ 'cancer' ] )) ? ($rows[ 'cancer' ]) : '',
            'causalgia' => (!empty( $rows[ 'causalgia' ] )) ? ($rows[ 'causalgia' ]) : '',
            'demyelinating_polyneuropathy' => (!empty( $rows[ 'chronic_inflammatory_demyelinating_polyneuropathy' ] ))
                ? ($rows[ 'chronic_inflammatory_demyelinating_polyneuropathy' ]) : '',
            'nervous_disorders' => (!empty( $rows[ 'chronic_nervous_system_disorders' ] ))
                ? ($rows[ 'chronic_nervous_system_disorders' ]) : '',
            'chronic_pain' => (!empty( $rows[ 'chronic_pain' ] )) ? ($rows[ 'chronic_pain' ]) : '',
            'chronic_pancreatitis' => (!empty( $rows[ 'chronic_pancreatitis' ] )) ?
                ($rows[ 'chronic_pancreatitis' ]) : '',
            'crohn' => (!empty( $rows[ 'crohns_disease' ] )) ?
                ($rows[ 'crohns_disease' ]) : '',
            'regional_pain_type2' => (!empty( $rows[ 'complex_regional_pain_syndrome_type_ii' ] )) ?
                ($rows[ 'complex_regional_pain_syndrome_type_ii' ]) : '',
            'decompensated_cirrhosis' => (!empty( $rows[ 'decompensated_cirrhosis' ] )) ?
                ($rows[ 'decompensated_cirrhosis' ]) : '',
            'dystonia' => (!empty( $rows[ 'dystonia' ] )) ? ($rows[ 'dystonia' ]) : '',
            'ehlers_danlos' => (!empty( $rows[ 'ehlers_danlos_syndrome' ] )) ? ($rows[ 'ehlers_danlos_syndrome' ]) : '',
            'epilepsy' => (!empty( $rows[ 'epilepsy' ] )) ? ($rows[ 'epilepsy' ]) : '',
            'interoccular_pressure' => (!empty( $rows[ 'elevated_interoccular_pressure' ] )) ?
                ($rows[ 'elevated_interoccular_pressure' ]) : '',
            'fibromyalgia' => (!empty( $rows[ 'fibromyalgia' ] )) ? ($rows[ 'fibromyalgia' ]) : '',
            'fibrous_dysplasia' => (!empty( $rows[ 'fibrous_dysplasia' ] )) ? ($rows[ 'fibrous_dysplasia' ]) : '',
            'glaucoma' => (!empty( $rows[ 'glaucoma' ] )) ? ($rows[ 'glaucoma' ]) : '',
            'hepatitis_C' => (!empty( $rows[ 'hepatitis_c' ] )) ? ($rows[ 'hepatitis_c' ]) : '',
            'HIV_AIDS' => (!empty( $rows[ 'hiv_or_aids' ] )) ? ($rows[ 'hiv_or_aids' ]) : '',
            'huntington' => (!empty( $rows[ 'huntingtons_disease' ] )) ? ($rows[ 'huntingtons_disease' ]) : '',
            'hydrocephalus' => (!empty( $rows[ 'hydrocephalus' ] )) ? ($rows[ 'hydrocephalus' ]) : '',
            'hydromyelia' => (!empty( $rows[ 'hydromyelia' ] )) ? ($rows[ 'hydromyelia' ]) : '',
            'intractible_spasticity' => (!empty( $rows[ 'inflamatory_bowel_disease' ] )) ?
                ($rows[ 'inflamatory_bowel_disease' ]) : '',
            'interstitial_cystitis' => (!empty( $rows[ 'interstitial_cystitis' ] )) ?
                ($rows[ 'interstitial_cystitis' ]) : '',
            'intractible_spasticity' => (!empty( $rows[ 'intractible_spasticity' ] )) ?
                ($rows[ 'intractible_spasticity' ]) : '',
            'lupus' => (!empty( $rows[ 'lupus' ] )) ? ($rows[ 'lupus' ]) : '',
            'migraine' => (!empty( $rows[ 'migraine' ] )) ? ($rows[ 'migraine' ]) : '',
            'mitochondiral_disease' => (!empty( $rows[ 'mitochondiral_disease' ] )) ?
                ($rows[ 'mitochondiral_disease' ]) : '',
            'multiple_sclerosis' => (!empty( $rows[ 'multiple_sclerosis' ] )) ? ($rows[ 'multiple_sclerosis' ]) : '',
            'muscular_dystrophy' => (!empty( $rows[ 'muscular_dystrophy' ] )) ? ($rows[ 'muscular_dystrophy' ]) : '',
            'myasthenia_gravis' => (!empty( $rows[ 'myasthenia_gravis' ] )) ? ($rows[ 'myasthenia_gravis' ]) : '',
            'myoclonus' => (!empty( $rows[ 'myoclonus' ] )) ? ($rows[ 'myoclonus' ]) : '',
            'nail_patella' => (!empty( $rows[ 'nail_patella' ] )) ? ($rows[ 'nail_patella' ]) : '',
            'nausea' => (!empty( $rows[ 'nausea' ] )) ? ($rows[ 'nausea' ]) : '',
            'neurofibromatosis' => (!empty( $rows[ 'neurofibromatosis' ] )) ? ($rows[ 'neurofibromatosis' ]) : '',
            'dataset_id' => $dataset,
        );
    }


    public function investmentThreshold($rows, $dataSetId,$latest)
    {
        return array(
            'segment' => ($rows[ 'segment' ] !== null ? strval($rows[ 'segment' ]) : ''),
            'low_medium' => ($rows[ 'low_medium' ] !== null ? strval($rows[ 'low_medium' ]) : ''),
            'medium_high' => ($rows[ 'medium_high' ] !== null ? strval($rows[ 'medium_high' ]) : ''),
            'dataset_id' => ($dataSetId !== null ? strval($dataSetId) : 0),
            'latest' => ($latest !== null ? strval($latest) : 0)
        );
    }

    public function investmentRankState($rows, $dataSetId,$latest)
    {
        return array(
            'state' => $rows[ 'state' ] !== null ? strval($rows[ 'state' ]) : '',
            'legalization' => $rows[ 'legalization' ] !== null ? strval($rows[ 'legalization' ]) : '',
            'cultivation' => 	$rows[ 'cultivation' ] !== null ? strval($rows[ 'cultivation' ]) : '',
            'retail' => 	$rows[ 'retail' ] !== null ? strval($rows[ 'retail' ]) : '',
            'manufacturing' => 	$rows[ 'manufacturing' ] !== null ? strval($rows[ 'manufacturing' ]) : '',
            'distribution' => 	$rows[ 'distribution' ] !== null ? strval($rows[ 'distribution' ]) : '',
            'ancillary' => 	$rows[ 'ancillary' ] !== null ? strval($rows[ 'ancillary' ]) : '',
            'risk' => 	$rows[ 'risk' ] !== null ? strval($rows[ 'risk' ]) : '',
            'opportunity' => 	$rows[ 'opportunity' ] !== null ? strval($rows[ 'opportunity' ]) : '',
            'description' => 	$rows[ 'description' ] !== null ? strval($rows[ 'description' ]) : '',
            'dataset_id' => (!empty( $dataSetId ) ? ($dataSetId) : 0),
            'latest' => (!empty( $latest ) ? ($latest) : 0)
        );
    }

    public function chart($rows, $reportId)
    {
        return array(
            'title' => (!empty( $rows[ 'chart_name' ] )) ? ($rows[ 'chart_name' ]) : '',
            'chartfilename' => (!empty( $rows[ 'chart_file_name' ] )) ? ($rows[ 'chart_file_name' ]) : '',
            'reportname' => (!empty( $rows[ 'report_name' ] )) ?
                ($rows[ 'report_name' ]) : '',
            'reportfilename' => (!empty( $rows[ 'report_file_name' ] )) ?
                ($rows[ 'report_file_name' ]) : '',
            'keywords' => (!empty( $rows[ 'keywords' ] )) ?
                ($rows[ 'keywords' ]) : '',
            'available' => 1,
            'report_id' => (!empty( $reportId ) ? ($reportId) : 0)
        );
    }

    public function taxRates($rows, $dataset)
    {
        return array();
    }

    public function cannabisBenchmarksUs($rows, $dataset)
    {
        return array();
    }

}



