<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;

class SubscriptionRepository extends Repository
{
    public function model()
    {
        return 'App\Models\SubscriptonRoute';
    }


    public function subscriptionsByuser($user, $route)
    {
        $result = $this->model->select('subscription_route.id')
            ->join('subscription', 'subscription.id', 'subscription_route.subscription_id')
            ->join('route', 'route.id', 'subscription_route.route_id')
            ->join('users', 'users.subscription_level', 'subscription.code')
            ->where('users.id', '=', $user)
            ->where('route.routes', '=', $route)
            ->get()->count();

        return $result;
    }


}
