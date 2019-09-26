<?php

namespace App\Repositories;

use App\Equio\Helper;
use App\Models\CompanyNewsInformaiton;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Config;


class CompanyNewsRepository extends Repository
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
        return 'App\Models\CompanyNewsInformaiton';
    }


    public function allCompanyNewInfo($request)
    {
        $data = Config::get('custom_config.COMPANY_NEWS_INFORMATION');
        $result = [];
        foreach ($data as $key => $value) {

            $result[] = ['id' => $key, 'name' => $value];

        }

        return $result;
    }

    public function allNewsDetailInfo($request)
    {
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'asc';
        $this->sortColumn = (!empty($request['sortType'])) ?
            ($request['sortType']) :
            'company_news_informaiton.name';
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        $columns = ['*'];
        $or = false;
        $elect = false;

        $data['data'] = [];

        $this->model = new CompanyNewsInformaiton();
        $model = $this->model
            ->select(
                [
                    'id', 'name'
                ]);

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
        $model = $model->orderBy($this->sortColumn, $this->sort)
            ->get();

        $data = $model;
        return $data;
    }


}
