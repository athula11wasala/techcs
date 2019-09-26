<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use StudioNet\ScoreSearch\Searchable;
use Illuminate\Support\Facades\Config;

class Chart extends Model
{

    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $table = "charts";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'report_id', 'reportname', 'title', 'chartfilename', 'reportfilename', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    protected $searchable =  [
        'joins' => ['reports'],
        'joins' => ['reports' => ['charts.report_id', 'reports.id']],
        'columns' => [
            'charts.title' => 10,
            'reports.name' => 8,
            'charts.keywords' => 6
        ]
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getchartfilenameAttribute($value)
    {
        return url('/') . "/storage/reports/charts/" . $value;
    }

    public function getreportfilenameAttribute($value)
    {
        return url('/') . "/storage/reports/enterprise_pdf/" . $value;
    }

    public function getThumbnailAttribute($value)
    {
        return url('/') . "/storage/reports/charts/" . $value;
    }

    public function getPdfurlAttribute($value)
    {

        return null;
        // return url('/') . "/storage/reports/enterprise_pdf/" . $value;
    }





}