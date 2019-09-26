<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\DB;

class UserProfileRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\UserProfile';
    }

    public function saveUserProfile($user_profile_array)
    {
       $user = $this->create($user_profile_array);
        return $user;
    }

    public function updateUserProfile($user_profile_array, $user_id)
    {
        $user = $this->update($user_profile_array, $user_id, 'user_id');
        return $user;

    }

}