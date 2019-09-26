<?php


namespace App\Repositories;

use App\Equio\Helper;
use Bosnadev\Repositories\Eloquent\Repository;
use DB;
use Illuminate\Support\Facades\Auth;

class PaymentRecordRepository extends Repository
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
        return 'App\Models\PaymentRecord';
    }

    public function allPaymentRecordInfo($request)
    {
        $this->perPage = env('PAGINATE_PER_PAGE', 15);
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'payment_records.id';

        $columns = ['*'];
        $or = false;
        $elect = false;
        $request = Helper::arrayUnset($request, ['sortType', 'perPage', 'sort', 'page',]);
        $userId = Auth::user()->id;

        $this->applyCriteria();

        $model = $this->model
            ->select(
                [
                    "id", "payment_status", "item_name", "payment_amount", 'order_id',
                    DB::raw("DATE_FORMAT(payment_date,'%m/%d/%Y')as payment_date")
                ]);

        //if (Helper::checkAdminstratorRole($userId) == false) {

        $model = $model->where("user_id", $userId);
        // }

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
