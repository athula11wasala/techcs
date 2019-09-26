<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use StudioNet\ScoreSearch\Searchable;
use Illuminate\Support\Facades\Config;

class Profiles extends Model
{

    use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "profiles";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'type', 'name', 'ticker', 'logo_url', 'company_logo', 'cover', 'full_pdf', 'description',
        'status', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $searchable ;

    public function __construct()
    {
        $this->searchable =  Config::get ( 'searchscore.PROFILE' );
    }



    public function getProfileDocumentAttribute($value)
    {
        return url ( '/' ) . "/storage/profiles/company/full_pdf/" . $value;
    }

    public function getProfileCoverAttribute($value)
    {
        return url ( '/' ) . "/storage/profiles/company/cover/" . $value;
    }

    public function getCompanyLogoAttribute($value)
    {
        return url ( '/' ) . "/storage/profiles/company/logo/" . $value;
    }

    public function getCreatedAtAttribute($value)
    {
        return !empty( $value ) ? date ( 'm/d/Y H:i:s', strtotime ( $value ) ) : '';
    }

    public function getUpdatedAtAttribute($value)
    {
        return !empty( $value ) ? date ( 'm/d/Y H:i:s', strtotime ( $value ) ) : '';
    }

    public function getThumbnailAttribute($value)
    {
        return url ( '/' ) . "/storage/profiles/company/cover/" . $value;
    }

    public function getPdfurlAttribute($value)
    {
        return url ( '/' ) . "/storage/profiles/company/full_pdf/" . $value;
    }

    public function getDescriptionAttribute($value)
    {

        if ( !empty( $value ) ) {
            $length = strlen ( $value );

            if ( $length >= 500 ) {

                return substr ( $value, 0, 500 ) . "...";
            }
            return $value;
        }
        return '';

    }


}
