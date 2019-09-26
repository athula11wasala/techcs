<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\Profiles;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class CompanyProfilesRepository extends Repository
{


    protected $perPage;
    protected $sort;
    protected $sortColumn;
    protected $is_backend;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Profiles';
    }

    /**
     * Returns basic comapany info by Name
     * @param $name
     * @return mixed
     */
    public function comapnyInfoByName($name)
    {
        return $this->model
            ->select(
                [
                    "id", "name", "type as profile_type", "cover as profile_cover", "full_pdf as profile_document",
                    "created_at", "updated_at", "status"
                ])
            ->where('name', '=', $name)
            // ->where ( 'status', 1 )
            ->first();
    }

    /**
     * Returns basic comapany info by Id
     * @param $id
     * @return mixed
     */
    public function comapnyInfoById($id)
    {

        return $this->model
            ->select(
                [
                    "id", "name", "type as profile_type", "cover as profile_cover", "full_pdf as profile_document",
                    "company_logo", "ticker", "description",
                    "created_at", "updated_at", "status", "full_pdf", "cover"
                ])
            ->where(function ($query) use ($id) {
                if ($id) {

                    $query->where("id", $id);
                }
            })
            //   ->where ( 'status', 1 )
            ->first();

    }


    /**
     * Returns all comapany info
     *
     * @return mixed
     */
    public function allComapnyDetail($request)
    {
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->perPage = (!empty($request['perPage'])) ? ($request['perPage']) : '15';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'updated_at';
        $this->limit = (!empty($request['limit'])) ? ($request['limit']) : 100;
        $request['type'] = (!empty($request['profile_type'])) ? ($request['profile_type']) : '';
        $this->is_backend = (!empty($request['is_backend'])) ? ($request['is_backend']) : null;

        $columns = ['*'];
        $or = false;

        if (!empty($request['company'])) {
            $request['name'] = $request['company'];
        }
        if (!empty($request['country'])) {
            $request['name'] = $request['country'];
        }

        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page', 'profile_type', 'company', 'country', 'is_backend']);

        $model = $this->model
            ->select("id", "name", "type as profile_type", "cover as profile_cover",
                "company_logo", "ticker", "description",
                "full_pdf as profile_document", "created_at", "updated_at", "status",
                DB::raw("(case  when type = '1' then name   else ''  end) AS company"),
                DB::raw("(case when type = '2' then name  else ''  end) AS country"));

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


        if (!empty($this->is_backend)) {

            $result = $model
                ->orderBy($this->sortColumn, $this->sort)
                ->paginate($this->perPage);

        } else {

            $result = $model
                ->where('status', 1)
                ->orderBy($this->sortColumn, $this->sort)
                ->paginate($this->perPage);

        }


        if (count($result) == 0) {
            return false;
        }

        return $result;
    }


    public function saveCompany($company_array)
    {
        \Log::info($company_array);
        \Log::info(" -----------============================ ");
        $type = !empty($company_array['profile_type']) ? $company_array['profile_type'] : 0;
        $description = !empty($company_array['description']) ? $company_array['description'] : '';
        $ticker = !empty($company_array['ticker']) ? $company_array['ticker'] : '';

        $name = !empty($company_array['name']) ? $company_array['name'] : '';
        $status = 1;
        if (isset($company_array['status'])) {

            if (($company_array['status'] == 'true')) {
                $status = 0;
            } else {
                $status = 1;
            }
        }
        $objProfiles = new Profiles();
        $objProfiles->name = $name;
        $objProfiles->ticker = $ticker;
        $objProfiles->type = $type;
        $objProfiles->description = $description;
        $objProfiles->status = $status;
        $objProfiles->save();

        return $this->uploadCompanyProfile($company_array, 'POST', $objProfiles->id);
    }


    public function updateCompany($company_array)
    {
        \Log::info($company_array);
        \Log::info(" &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& ");

        $name = "";
        $type = !empty($company_array['profile_type']) ? $company_array['profile_type'] : 0;
        $description = !empty($company_array['description']) ? $company_array['description'] : '';
        $ticker = !empty($company_array['ticker']) ? $company_array['ticker'] : '';
        $name = !empty($company_array['name']) ? $company_array['name'] : '';

        $status = 1;
        if ($company_array['status'] === 'true') {
            $status = 0;
        } else {
            $status = 1;
        }

        if ($this->model->where('id', '=', $company_array['id'])
            ->update(['name' => $name, 'type' => $type, 'ticker' => $ticker, 'status' => $status,
                'description' => $description])) {

            if (!empty($company_array['profile_cover'])
                || !empty($company_array['profile_document'])
                || !empty($company_array['company_logo'])) {
                return $this->uploadCompanyProfile($company_array, 'PUT', true);
            } else {
                return true;
            }

        }

    }

    public function uploadCompanyProfile($company_array, $request = 'POST', $id = null, $flag = false)
    {

        $objCompanyProfile = Profiles::find(isset($company_array['id']) ? $company_array['id'] : $id);

        if ($company_array['profile_cover']) {

            $image = $company_array['profile_cover'];
            // $fileName = $objCompanyProfile->id . "_" . rand() . '_' . $image->getClientOriginalName();
            $fileName = $objCompanyProfile->id . "_" . (string)$image->getClientOriginalName();

            if ($request == 'PUT') {

                $existImgName = $objCompanyProfile->cover;
                $imageName = explode("/", $existImgName);

                if (!empty($imageName)) {

                    \File::delete(public_path(Config::get('custom_config.company_profile_cover')) . "/"
                        . $imageName[(count($imageName) - 1)]);
                }

            }

            if (!file_exists(Config::get('custom_config.company_profile_cover'))) {
                mkdir(Config::get('custom_config.company_profile_cover'), 0777, true);
            }
            $destinationPath = Config::get('custom_config.company_profile_cover');
            $company_array['profile_cover']->move($destinationPath, $fileName);

            //$objCompanyProfile->cover = $destinationPath . "/" . $fileName;

            $objCompanyProfile->cover = $fileName;

            if ($objCompanyProfile->save()) {
                $flag = true;
            }

        }

        if ($company_array['profile_document']) {
            $image = $company_array['profile_document'];
            $fileName = $objCompanyProfile->id . "_" . (string)$image->getClientOriginalName();

            if ($request == 'PUT') {

                $existPdfName = $objCompanyProfile->full_pdf;
                $pdfeName = explode("/", $existPdfName);

                if (!empty($pdfeName)) {

                    \File::delete(public_path(Config::get('custom_config.company_profile_document'))
                        . "/" . $pdfeName[(count($pdfeName) - 1)]);
                }

            }
            if (!file_exists(Config::get('custom_config.company_profile_document'))) {
                mkdir(Config::get('custom_config.company_profile_document'), 0777, true);
            }
            $destinationPath = Config::get('custom_config.company_profile_document');
            $company_array['profile_document']->move($destinationPath, $fileName);

            $objCompanyProfile->full_pdf = $fileName;
            if ($objCompanyProfile->save()) {
                $flag = true;
            }

        }
        if ($company_array['company_logo']) {
            $image = $company_array['company_logo'];
            $fileName = $objCompanyProfile->id . "_" . (string)$image->getClientOriginalName();

            if ($request == 'PUT') {

                $existLogoName = $objCompanyProfile->company_logo;
                $logoName = explode("/", $existLogoName);

                if (!empty($logoName)) {

                    \File::delete(public_path(Config::get('custom_config.company_profile_logo'))
                        . "/" . $logoName[(count($logoName) - 1)]);
                }

            }
            if (!file_exists(Config::get('custom_config.company_profile_logo'))) {
                mkdir(Config::get('custom_config.company_profile_logo'), 0777, true);
            }
            $destinationPath = Config::get('custom_config.company_profile_logo');
            $company_array['company_logo']->move($destinationPath, $fileName);

            $objCompanyProfile->company_logo = $fileName;
            if ($objCompanyProfile->save()) {
                $flag = true;
            }

        }


        return $flag;
    }

    public function deleteCompany($request)
    {
        return $this->model->where('id', '=', $request['id'])
            ->update(['status' => 0]);

    }

    public function allComapnyInfo($request)
    {

        $this->perPage = env('PAGINATE_PER_PAGE', 15);
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'profiles.updated_at';
        $this->is_backend = (!empty($request['is_backend'])) ? ($request['is_backend']) : null;

        if (!empty($request['perPage'])) {
            $this->perPage = $request['perPage'];
        }
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page', 'is_backend']);

        return $this->findWhere($request);

    }

    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria();

        $model = $this->model;

        foreach ($where as $field => $value) {
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
        $model = $model->select("id", "name", "type as 	profile_type", "cover as profile_cover",
            "company_logo", "ticker", "description",
            "full_pdf as profile_document", "created_at", "updated_at", "status",
            DB::raw("(case  when type = '1' then name   else ''    end) AS company"),
            DB::raw("(case when type = '2' then name  else ''  end) AS country"));

        $model = $model->where("type",2);

        if (!empty($this->is_backend)) {

            return $model
                ->orderBy($this->sortColumn, $this->sort)
                ->paginate($this->perPage);

        } else {

            return $model->where('status', 1)
                ->orderBy($this->sortColumn, $this->sort)
                ->paginate($this->perPage);
        }


    }


}
