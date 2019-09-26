<?php


namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;


class CanaClipsRepository extends Repository
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
        return 'App\Models\Cannaclips';
    }


    public function allCannaClipsInfo($request)
    {
        $this->perPage = env('PAGINATE_PER_PAGE', 15);
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'des';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'cannaclips.id';
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page']);

        return $this->findWhere($request);
    }


    public function findWhere($where, $columns = ['*'], $or = false, $elect = false)
    {
        $this->applyCriteria();

        $model = $this->model
            ->select(
                [
                    "id", "name", "description", "link", "link as thumbnail"
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

        $model = $model->orderBy($this->sortColumn, $this->sort);
        return $model->paginate($this->perPage);

    }


}