<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use App\Models\Report;

class AuditLog extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "audit_log";

    protected $primaryKey = 'id';
    protected $type = '';

    protected $fillable = [
        'id', 'user_id', 'action', 'object_id', 'object_table', 'object_column', 'location', 'created_at', 'updated_at'
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    public function getObjectTypeAttribute($value)
    {

        if ( !empty( $value ) ) {
            $this->type = $value;
            return ucfirst ( $value );
        }
        return '';
    }


    public function getobjectWooIdAttribute($value)
    {
        if ( isset( $this->type ) ) {

            if ( $this->type == "reports" ) {
                $objReport = Report::where ( "id", $value )->select ( "woo_id" )->first ();

                return !empty( $objReport->woo_id ) ? $objReport->woo_id : '';
            }
        }
        return 'NA';
    }



}
