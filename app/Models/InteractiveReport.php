<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class InteractiveReport extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "interactive_reports";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'report_id', 'cannaclip', 'summary', 'author', 'analysts', 'leader', 'editor', 'marketing', 'key_findings_text', 'key_findings_list', 'author_image', 'cover_image', 'author_headshot',
    ];
    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    public function getAuthorHeadshotAttribute($value)
    {

        if ( isset( $value) ) {
            $data =   url ( '/' ) .  '/'. ltrim ( Config::get ( 'custom_config.REPORTS_STORAGE' ), "/" )  . Config::get ( 'custom_config.INTERACTIVE_AUTHOR' ). $value;

            return $data;
        }
        return '';
    }


}

