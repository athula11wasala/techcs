<?php

namespace App\Models;

use App\Equio\Helper;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Laravel\Cashier\Billable;
use App\Models\RoleUser;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, EntrustUserTrait;

    //use Billable;

    /**
     * Primary key
     * @var string
     */
    protected $primaryKey = 'id';
    protected $newsHeaderId = '';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'encrypted_password', 'password', 'subscription_plan', 'trial_period', 'paid_subscription_end', 'paid_subscription_start'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'encrypted_password', 'remember_token', 'sign_in_count', 'current_sign_in_at', 'last_sign_in_at',
        'current_sign_in_ip', 'last_sign_in_ip', 'reset_password_token', 'reset_password_sent_at', 'remember_created_at', 'trial_period'
    ];

    public function profile()
    {
        return $this->hasOne ( 'App\Models\UserProfile' );
    }


    public function getIndustryRoleAttribute($value)
    {

        if ( isset( Config::get ( 'custom_config.INDUSTRY_ROLE' )[ $value ] ) ) {

            return Config::get ( 'custom_config.INDUSTRY_ROLE' )[ $value ];

        }
    }

    public function getPositionAttribute($value)
    {

        if ( isset( Config::get ( 'custom_config.USER_POSITION' )[ $value ] ) ) {

            return Config::get ( 'custom_config.USER_POSITION' )[ $value ];

        }

    }


    public function getNewsCompanyHeaderAttribute($value)
    {

        if ( isset( Config::get ( 'custom_config.COMPANY_NEWS_INFORMATION' )[ $value ] ) ) {

            return Config::get ( 'custom_config.COMPANY_NEWS_INFORMATION' )[ $value ];

        }

    }

    public function getCountryAttribute($value)
    {

        if ( is_numeric ( $value ) ) {

            $country = Country::find ( $value );
            return $country->name;

        } elseif ( is_string ( $value ) ) {

            return $value;

        } else {

            return $value;
        }


    }

    public function getStateAttribute($value)
    {

        if ( is_numeric ( $value ) ) {

            $country = \App\Models\State::find ( $value );
            return $country->name;

        } elseif ( is_string ( $value ) ) {

            return $value;

        } else {

            return $value;
        }


    }


    public function ssssgetStateIdAttribute($value)
    {

        if ( is_numeric ( $value ) ) {

            $country = \App\Models\State::find ( $value );
            return $country->name;

        } elseif ( is_string ( $value ) ) {

            return $value;

        } else {

            return $value;
        }


    }


    public function getNewsCompnyDetailAttribute($value)
    {
        $objNewHeaderDetail = CompanyNewsInformaiton::find ( $value );

        if ( !empty( $objNewHeaderDetail ) ) {
            return $objNewHeaderDetail->name;
        }
        return '';

    }

    public function roles()
    {
        return $this->belongsToMany ( 'App\Models\Role', 'role_user', 'user_id', 'role_id' );
    }

    public function getRoleStateAttribute($value)
    {
        $role = "";
        $objPermission = Helper::userRolesAndPermissions ( $value );
        $i = 0;
        foreach ( $objPermission as $rows ) {
            $i++;
            if ( $i > 1 ) {
                $role .= ",";
            }

            $role .= ucfirst ( strtolower ( $rows->name ) );
        }
        return $role;
    }

}
