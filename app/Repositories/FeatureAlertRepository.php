<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\FeatureAlert;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class FeatureAlertRepository extends Repository
{


    protected $perPage;
    protected $sort;
    protected $sortColumn;
    protected $limit;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\FeatureAlert';
    }


    /**
     * Returns all feature alert info
     *
     * @param $request
     * @return mixed
     */
    public function getAllAlert($request)
    {
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->perPage = (!empty($request['perPage'])) ? ($request['perPage']) : '10';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'updated_at';
        $this->limit = (!empty($request['limit'])) ? ($request['limit']) : 100;

        $columns = ['*'];
        $or = false;
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);


        $model = $this->model->select(
            "id",
            "title",
            "description",
            "image",
            "link",
            "active",
            "created_at",
            "updated_at"
        );

        foreach ($request as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, 'like', '%' . $value . '%')
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, 'like', '%' . $value . '%')
                    : $model->orWhere($field, '=', $value);
            }
        }


        $result = $model
            ->orderBy('active', 'DESC')
            ->orderBy($this->sortColumn, $this->sort)
            ->paginate($this->perPage);

        return $result;
    }


    public function checkExistFeatureTitle($title = null, $id = null)
    {
        $exitRecord = true;
        $retrunArr = ['success' => 'true'];
        $model = $this->model->select("id");

        if (!empty($title) && !empty($id)) {

            $checkExitTitle = $this->model->select("id")->where('id', $id)->first();
            $model = $model->where("title", $title)->first();


            if (!empty($checkExitTitle) & !empty($model)) {

                if (($checkExitTitle->id) != ($model->id)) {

                    $retrunArr = ['success' => 'fail'];
                }

            }


        }
        if (!empty($title) && empty($id)) {
            $model = $model->where("title", $title)->first();

            if (!empty($model)) {
                $retrunArr = ['success' => 'fail'];
            }

        }

        return $retrunArr;

    }


    /**
     * @param $alert_array
     * @return bool
     */
    public function saveAlert($alert_array)
    {
        $title = !empty($alert_array['title']) ? $alert_array['title'] : 0;
        $description = !empty($alert_array['description']) ? $alert_array['description'] : '';
        $link = !empty($alert_array['link']) ? $alert_array['link'] : '';
        $active = !empty($alert_array['active']) ? $alert_array['active'] : 0;
        $order = !empty($alert_array['order']) ? $alert_array['order'] : 0;


        $featureAlert = $this->model->create([
            'title' => $title,
            'description' => $description,
            'image' => '',
            'link' => $link,
            'active' => $active,
            'order' => $order
        ]);

        if (!empty($alert_array['image'])) {
            return $this->uploadAlertImage($alert_array, 'POST', $featureAlert->id);
        } else {
            return true;
        }

    }


    public function updateFeatureStatus($request)
    {
        $flag = false;
        $alert_array = !empty($request) ? $request : [];
        if (isset($alert_array['data'])) {
            if (gettype($alert_array['data']) == 'array') {
                foreach ($alert_array['data'] as $rows) {
                    if ($this->model->where("id", $rows['id'])->update(['active' => $rows['status']])) {
                        $flag = true;
                    }
                }
            }
        }
        return $flag;
    }

    public function updateFeature($alert_array)
    {

        $objNewFeature = $this->model->select("*")->where("id", $alert_array['id'])->first();
        $title = !empty($alert_array['title']) ? $alert_array['title'] : 0;
        $description = !empty($alert_array['description']) ? $alert_array['description'] : '';
        $link = !empty($alert_array['link']) ? $alert_array['link'] : '';
        $active = !empty($objNewFeature->active) ? $objNewFeature->active : 0;
        $order = !empty($objNewFeature->order) ? $objNewFeature->order : 0;


        if (isset($alert_array['order'])) {

            if (is_numeric($alert_array['order'])) {

                $order = $alert_array['order'];;
            }


        }


        if (isset($alert_array['active'])) {

            if (is_numeric($alert_array['active'])) {

                $active = ($alert_array['active'] == 0) ? 0 : 1;
            }


        }

        if ($this->model->where('id', '=', $alert_array['id'])
            ->update(['title' => $title, 'description' => $description, 'link' => $link, 'active' => $active,
                'order' => $order])) {

            DB::table("user_acknowledgements")->where("feature_id", $alert_array['id'])->delete();
            if (gettype($alert_array['image']) == 'object') {
                if ($alert_array['image']) {
                    return $this->uploadAlertImage($alert_array, 'PUT', $alert_array['id']);
                } else {
                    return true;
                }
            } else if ($alert_array['image'] === '0') {
                return $this->model->where('id', '=', $alert_array['id'])
                    ->update(['image' => '']);
            } else {
                return true;
            }

        }


    }


    /**
     * upload image
     * @param $alert_array
     * @param string $request
     * @param null $id
     * @param bool $flag
     * @return bool
     */
    public function uploadAlertImage($alert_array, $request = 'POST', $id = null, $flag = false)
    {

        $objAlertImage = FeatureAlert::find(isset($alert_array['id']) ? $alert_array['id'] : $id);
        if (!empty($alert_array['image'])) {

            if (gettype($alert_array['image']) == 'object') {

                $image = $alert_array['image'];
                $fileName = $objAlertImage->id . "_" . (string)$image->getClientOriginalName();

                if ($request == 'PUT') {

                    $existImgName = $objAlertImage->image;
                    $imageName = explode("/", $existImgName);

                    if (!empty($imageName)) {

                        \File::delete(public_path(Config::get('custom_config.alert_image')) . "/"
                            . $imageName[(count($imageName) - 1)]);
                    }

                }

                if (!file_exists(Config::get('custom_config.alert_image'))) {
                    mkdir(Config::get('custom_config.alert_image'), 0777, true);
                }
                $destinationPath = Config::get('custom_config.alert_image');
                $alert_array['image']->move($destinationPath, $fileName);

                $objAlertImage->image = $fileName;

                if ($objAlertImage->save()) {
                    $flag = true;
                }
                $flag = true;
            }
        } else if ($alert_array['image'] == 0) {
            $objAlertImage->image = '';

            if ($objAlertImage->save()) {
                $flag = true;
            }
            $flag = true;
        }

        return $flag;
    }

    /**
     * get notification by user
     * @param $user
     * @return bool
     */
    public function getAllNotification($user)
    {
        $result = $this->model
            ->select('new_features.*')
            ->whereRaw('new_features.id NOT IN (SELECT user_acknowledgements.feature_id FROM user_acknowledgements WHERE user_acknowledgements.user_id = ' . $user . ')')
            ->where('new_features.active', 1)
            ->orderBy("updated_at", "desc")
            ->get();

        if (count($result) == 0) {
            return false;
        }

        return $result;
    }

    /**
     * Returns basic feature info by Id
     * @param $id
     * @return mixed
     */
    public function featureInfoById($id)
    {

        return $this->model
            ->select(
                [
                    "id", "title", "description", "image",
                    "link", "active", "order",
                    "created_at", "updated_at" , "image as cover"
                ])
            ->where(function ($query) use ($id) {
                if ($id) {

                    $query->where("id", $id);
                }
            })
            ->first();

    }


}
