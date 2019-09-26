<?php


namespace App\Repositories\Criteria\Users;

use Bosnadev\Repositories\Criteria\Criteria;
use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;
use Bosnadev\Repositories\Criteria\RepositoryInterface;

class AllUsersSelect extends Criteria
{
    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {


        $model = $model->select([
            'user_profiles.*',
            'users.*',
            \DB::raw(
                "( CASE WHEN DATE(users.paid_subscription_start) = DATE(users.trial_datetime) && DATE(paid_subscription_start) <= CURDATE()
                                         && (paid_subscription_end) >= CURDATE() && DISABLE = '0' THEN 'in trial' 
                                 WHEN disable = '0' && trial_datetime != paid_subscription_start && paid_subscription_start <= CURDATE() && paid_subscription_end >= CURDATE() then 'paid' 
                                 when paid_subscription_end <= CURDATE() && disable = '0' then 'expired' 
                                 when disable = '1' then 'disabled' 
                                 ELSE '-' END)  AS status"
            ),
            \DB::raw("users.id AS role_state"),
          //  \DB::raw("(CASE users.role WHEN users.role = 1 THEN 'Admin' ELSE 'User' END) AS role_state"),
            \DB::raw("DATE_FORMAT(users.created_at,'%m/%d/%Y') as created_date"),
            \DB::raw("DATE_FORMAT(users.last_sign_in_at,'%m/%d/%Y') as last_signed_in_date"),
            \DB::raw(
                "(
                CASE WHEN users.paid_subscription_end != '' 
                THEN  DATE_FORMAT(users.paid_subscription_end,'%m/%d/%Y')   
                ELSE '-' END) AS renewal_date"
            ),

        ]);
        return $model;
    }
}



