<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxAlert extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = "mysql_external_intake";


    protected $table = 'taxalerts';

    public function getDateMeetingAttribute($value)
    {
        return    !empty( $value ) ? date ( 'm-d-Y', strtotime ( $value ) ) : '';
    }


}
