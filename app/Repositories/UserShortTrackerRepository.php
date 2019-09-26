<?php

namespace App\Repositories;


use App\Equio\Helper;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\UserProfile;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Bosnadev\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Models\UserShortTracker;

class UserShortTrackerRepository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model() {
        return 'App\Models\UserShortTracker';
    }


    public function getShortTrackerList() {
        $company_list = $this->model->select("symbol")->where( "user_id", Auth::user()->id )->get ();
        \Log::info("==== UserShortTrackerRepository->getShortTrackerList ", ['u' => json_encode(Auth::user()->id)]);
        \Log::info("==== UserShortTrackerRepository->getShortTrackerList ", ['u' => json_encode($company_list)]);
        return $company_list;
    }

    public function saveUserShortTracker($user_short_tracker_array) {
        $user = $this->create($user_short_tracker_array);
        return $user;
    }

    public function updateUserShortTracker($user_short_tracker_array, $user_id) {
        $user = $this->update($user_short_tracker_array, $user_id, 'user_id');
        return $user;
    }

    public function createUserShortTracker($userShortTrackersDataArr = array()){

        return $this->model->insert($userShortTrackersDataArr);
    }

    public function addExistCustomerSymbol($userShortTrackersDataArr = array(),$userId){

          DB::table('user_short_trackers')->where('user_id', $userId)->delete();
          return $this->model->insert($userShortTrackersDataArr);
    }

    public function deleteUserShortTracker($userId = null)
    {

        DB::table('user_short_trackers')->where('user_id', $userId)->delete();

    }

    public function getExistingCompanies($userId = null)
    {

        return DB::table('user_short_trackers')->select('symbol')->where('user_id', $userId)->get()->toArray();

    }
    
    public function updateBasicCompanies(
        $data = [], $userId = null
    ) {

        DB::table('user_short_trackers')
            ->where('user_id', $userId)
            ->whereNotIn('user_short_trackers.symbol', $data['additional_plan'])
            ->delete();

        return $this->model->insert($data['user_short_tracker_data']);
    }


}