<?php

namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;

class BundleReoportRepository extends Repository
{

    Protected $perPage;
    Protected $sort;
    Protected $sortColumn;
    Protected $limit;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Bundle';
    }


    public function allBundleReportInfo($request)
    {
        $this->perPage = env('PAGINATE_PER_PAGE', 15);
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'bundle.id';
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        return $this->findWhere($request->all());

    }

    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria();

        $model = $this->model
            ->select(
                [
                    "id", "name", "purchase_url_link", "cover_image", "price", "offer", "description"
                ]);

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
                        ? $model->Where($field, '=', $value)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->Where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }

        $model = $model->where("status", "1");
        $model = $model->orderBy($this->sortColumn, $this->sort);
        return $model->paginate($this->perPage);

    }

    public function allBundleName()
    {
        return $this->model
            ->select("id", "name")
            ->get();
    }

    public function getAvailableBundle($request)
    {
        return $this->model->select('bundle.*')
            ->where('bundle.status', "=", 1)
            ->whereNotIn('bundle.woo_id', $request)
            ->get();
    }


}