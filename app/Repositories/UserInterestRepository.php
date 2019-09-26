<?php

namespace App\Repositories;

use App\Models\Interest;
use App\Models\UserInterest;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Auth;


class UserInterestRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\UserInterest';
    }

    public function addInterest($data)
    {
        $deleteUserInterst = UserInterest::where("user_id", Auth::user()->id)->delete();
        $flag = false;
        if (!empty($data)) {
            foreach ($data as $rows) {
                $allInterest = Interest::where("id", $rows)->first();
                if (!empty($allInterest)) {
                    $objUserInterst = new UserInterest();
                    $objUserInterst->interest_id = $allInterest->id;
                    $objUserInterst->user_id = Auth::user()->id;
                    if ($objUserInterst->save()) {
                        $flag = true;
                    }
                }
            }
            return $flag;
        }
    }

    public function getUserIntestId($userId, $interstId)
    {
        $interstId = UserInterest::where("user_id", $userId)
            ->where("interest_id", $interstId)->first();
        if (!empty($interstId)) {
            return true;
        }
        return false;
    }

    public function viewUserInterst($userId)
    {
        $this->model = new  Interest;
        $userInterst = UserInterest::where("user_id", $userId)->get();
        $result = $this->model
            ->select(
                [
                    'id', 'name', 'type'
                ])
            ->orderBy("name", "asc")
            ->get();
        $data = [];
        $i = null;
        $check = false;
        foreach ($result as $rows) {
            if ($rows->type == 1) {
                $check = $this->getUserIntestId($userId, $rows->id);
                $data['type'][$rows->type][] = [
                    'id' => $rows->id,
                    'name' => $rows->name,
                    'check' => $check
                ];
            } elseif ($rows->type == 2) {

                $check = $this->getUserIntestId($userId, $rows->id);
                $data['type'][$rows->type][] = [
                    'id' => $rows->id,
                    'name' => $rows->name,
                    'check' => $check
                ];
            }
        }
        return $data;
    }
}
