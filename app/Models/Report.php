<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use StudioNet\ScoreSearch\Searchable;
use App\Equio\Helper;
use Illuminate\Support\Facades\DB;

class Report extends Model
{

    use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "reports";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'woo_id', 'name', 'state', 'business', 'cover', 'link', 'summary_pdf',
        'full_pdf', 'enterprise_pdf', 'available', 'segment', 'category', 'description', 'price', 'publish_at'
    ];
    public $timestamps = false;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $searchable = [
            'joins' => ['interactive_reports'],
            'joins' => ['interactive_reports' => ['interactive_reports.report_id', 'reports.id']],
            'columns' => [
                'reports.name' => 10,
                'reports.description' => 8,
                'reports.state' => 7,
                'reports.category' => 0,
                'interactive_reports.summary' => 4,
                'interactive_reports.author' => 0,
                'interactive_reports.analysts' => 0,
                'interactive_reports.leader' => 0,
                'interactive_reports.editor' => 0,
                'interactive_reports.marketing' => 0,
                'interactive_reports.key_findings_text' => 5,
                'interactive_reports.key_findings_list' => 4
            ]
        ];


    public function getsegmentAttribute($value)
    {

        if ( isset( Config::get ( 'custom_config.REPORT_SEGMENT' )[ $value ] ) ) {
            $data = Config::get ( 'custom_config.REPORT_SEGMENT' )[ $value ];
            return $data;
        }
        return '';
    }

    public function getCoverImageAttribute($value)
    {

        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_COVER' ) . $value;

            return $data;
        }
        return '';
    }

    public function getSimpleExecSummaryPdfAttribute($value)
    {

        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_SUMMERY_PDF' ) . $value;

            return $data;
        }
        return '';
    }

    public function getSimpleFullPdfAttribute($value)
    {

        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_FULL_PDF' ) . $value;

            return $data;
        }
        return '';
    }

    public function getSimpleEnterprisePdfAttribute($value)
    {

        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_ENTERPRISES_PDF' ) . $value;

            return $data;
        }
        return '';
    }

    public function getAuthorPhotoAttribute($value)
    {

        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.INTERACTIVE_AUTHOR' ) . $value;

            return $data;
        }
        return '';
    }

    public function getHardcopyWooIdAttribute($value)
    {

        return intval ( $value );
    }

    public function getSegmentsAttribute($value)
    {

        return intval ( $value );
    }

    public function getDigitalWooIdNameAttribute($value)
    {
        $objHelper = new Helper();

        return $objHelper->getWooCommerceRetiveId ( $value );
    }

    public function getHardcopyWooNameAttribute($value)
    {
        $objHelper = new Helper();

        return $objHelper->getWooCommerceRetiveId ( $value );
    }

    public function getInteractiveCanaclipImgAttribute($value)
    {
        return Helper::UrlExtesnionTCallThmbnil ( $value );

    }

    public function getTypeAttribute($value)
    {
        if ( empty( $value ) ) {
            return 'simple';
        }
        return 'interactive';

    }


    public function getCreatedAtAttribute($value)
    {
        return !empty( $value ) ? date ( 'm/d/Y H:i:s', strtotime ( $value ) ) : '';
    }

    public function getUpdatedAtAttribute($value)
    {
        return !empty( $value ) ? date ( 'm/d/Y H:i:s', strtotime ( $value ) ) : '';
    }

    public function getPublishAtAttribute($value)
    {
        return !empty( $value ) ? date ( 'm/d/Y H:i:s', strtotime ( $value ) ) : '';
    }


    public function getavailableAtAttribute($value)
    {

        return $value;
    }

    public function getThumbnailAttribute($value)
    {
        if ( isset( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_COVER' ) . $value;

            return $data;
        }
        return '';
    }


    public function getPdfurlAttribute($value)
    {
        if ( isset( $value ) ) {

            if ( Helper::checkEnterprisePdfAccess () == true ) {
                $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_ENTERPRISES_PDF' ) . $value;
            } else {
                $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_FULL_PDF' ) . $value;
            }

            return $data;
        }
        return '';
    }

    public static  function CoverImage($value = null)
    {

        if ( !empty( $value ) ) {
            $data = url ( '/' ) . '/' . ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" ) . Config::get ( 'custom_config.REPORTS_COVER' ) . $value;

            return $data;
        }
        return '';
    }

    /*public function getDescriptionAttribute($value)
    {

        if ( !empty( $value ) ) {
            $length = strlen ( $value );

            if ( $length >= 500 ) {

                return substr ( $value, 0, 500 ) . "...";
            }
            return $value;
        }
        return '';
    }*/

}



