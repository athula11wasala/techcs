<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Equio\Helper;
use App\Models\TopFive;

class TopFiveRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\TopFive';
    }

    /**
     * Returns basic user info by email
     * @param $email
     * @return mixed
     */
    public function getTopfive()
    {


        $checkCurrentDateResultId = $this->getCurrentDateJobId();

        if (count($checkCurrentDateResultId) > 0) {


            if (($this->getCurrentDateRecordCount()->count) > 4) {

                return $this->model->orderBy('id', 'desc')->whereIn("id", $checkCurrentDateResultId)
                    ->paginate(Config::get('custom_config.TOP_FIVE_PAGINATE'),
                        array('date', 'source', 'headline', 'full_story', 'image_url', 'topic', 'source_url',
                            'topic as category_image'));

            } else {

                return $this->model->whereIn("id",
                    $this->addFiveRecordsfromPreviousDate())
                    ->orderBy("id", "desc")
                    ->paginate(Config::get('custom_config.TOP_FIVE_PAGINATE'),
                        array('date', 'source', 'headline', 'full_story', 'image_url', 'topic', 'source_url',
                            'topic as category_image'));

            }

        } else {

            $nearstDate = $this->getNearstDateJob()->date ?? '0';
            $pastJobDetails = $this->model->orderBy("id", "desc")->where('date', $nearstDate)
                ->paginate(5, array('date', 'source', 'headline', 'full_story', 'image_url', 'topic', 'source_url',
                    'topic as category_image'));

            if (count($pastJobDetails) < 5) {

                return $this->model->whereIn("id",
                    $this->addFiveRecordsfromPreviousDate())
                    ->orderBy("id", "desc")
                    ->paginate(Config::get('custom_config.TOP_FIVE_PAGINATE'),
                        array('date', 'source', 'headline', 'full_story', 'image_url', 'topic', 'source_url',
                            'topic as category_image'));

            } else {

                return $pastJobDetails;
            }


        }


    }

    public function getCurrentDateRecordCount()
    {

        return $this->model
            ->selectRaw("count(id)as count")
            ->whereRaw('date = DATE(NOW())')
            ->first();

    }

    public function addFiveRecordsfromPreviousDate()
    {

        $retriveRecords = 5;
        $ids = [];

        $retriveRecords = intval($retriveRecords) - intval($this->getCurrentDateRecordCount()->count);

        $objCanbilization = $this->model
            ->selectRaw("id")
            ->whereRaw('date = DATE(NOW())')
            ->get();

        foreach ($objCanbilization as $rows) {
            array_push($ids, $rows->id);
        }

        $nearstDate = $this->getNearstDateJob()->date ?? '0';

        $nearstIds = $this->model
            ->selectRaw("id")
            ->whereRaw('date != DATE(NOW())')
            ->orderByRaw('ABS( DATEDIFF( date, NOW() ) )')
            ->orderBy('date', 'desc')
            ->limit($retriveRecords)
            ->get('id');

        foreach ($nearstIds as $rows) {
            array_push($ids, $rows->id);
        }

        return $ids;


    }

    public function getCurrentDateJobId()
    {

        return $this->model
            ->select("id")
            ->whereRaw('date = DATE(NOW())')
            ->get();

    }

    public function getNearstDateJob()
    {

        return $this->model
            ->select("date")
            ->orderByRaw('ABS( DATEDIFF( date, NOW() ) )')
            ->limit(1)
            ->first('date');
    }


    public function getIdOfNews()
    {
        return $this->model
            ->select("id")
            ->orderBy("id", "desc")
            ->limit(5)
            ->get();
    }

    public function getGroupYears()
    {
        return $this->model
            ->select(DB::raw('Extract(year from date)as years'))
            ->where("id", $this->getIdOfNews())
            ->limit(100)
            ->get();
    }

    public function getAllNews()
    {
        $currentDate = Carbon::now();
        $objTopFive = $this->model
            ->select(DB::raw('Extract(year from date)as years'), DB::raw('Extract(month from date)as month'), 'insight_daily_us.*')
            //->limit ( 100 )
            //->whereMonth ( 'date', '=', $currentDate->month )
            ->orderBy('date', 'desc')
            ->get();
        if ($objTopFive != null) {
            foreach ($objTopFive as $rows) {
                $data['year'][] = $rows;
            }
        } else {
            $data['year'] = null;
        }

        $data['last_year'] = $this->model
            ->select('date')
            ->orderBy('date', 'asc')
            ->first();

        $data['start_year'] = $this->model
            ->select('date')
            ->orderBy('date', 'desc')
            ->first();

        $data['topic'] = $this->model
            ->select('topic')
            ->groupBy('topic')
            ->get();


        return $data;
    }

    /**
     * @param $request
     */
    public function allNewsSearch($request)
    {
        $type = $request['type'];
        $search = $request['search'];
        $query = $this->model->select(
            DB::raw('Extract(year from date)as years'),
            DB::raw('Extract(month from date)as month'), 'insight_daily_us.*'
        );
        if ($type != null && $search != null) {
            if ($type == Config::get('custom_config.TOP_NEWS_SEARCH_BY_MONTH')) {
                $date = Carbon::parse($search);
                $query->whereMonth('date', '=', $date->month);
            } elseif ($type == Config::get('custom_config.TOP_NEWS_SEARCH_BY_YEAR')) {
                $query->whereYear('date', '=', $search);
            } elseif ($type == Config::get('custom_config.TOP_NEWS_SEARCH_BY_TOPIC')) {
                $query->where('topic', '=', $search);
            } elseif ($type == Config::get('custom_config.TOP_NEWS_SEARCH_BY_KEYWORD')) {
                $query->where('full_story', 'like', '%' . $search . '%');
            }
        }
        $query->orderBy('date', 'desc');
        $query->limit(100);
        $request = $query->get();
        foreach ($request as $rows) {
            $data['year'][] = $rows;
        }
        $data['last_year'] = $this->model
            ->select('date')
            ->orderBy('date', 'asc')
            ->first();

        $data['start_year'] = $this->model
            ->select('date')
            ->orderBy('date', 'desc')
            ->first();

        $data['topic'] = $this->model
            ->select('topic')
            ->groupBy('topic')
            ->get();

        return $data;
    }


    public function test($columNameWithVal)
    {
        return $this->model::search($columNameWithVal)->select('source as name', 'id as code', 'image_url as thumbnail', 'full_story as description')
            ->paginate();

    }


    /**
     * get insight daily search
     */
    public function getAllInsightDaily($request)
    {

        $this->keyword = (!empty($request['keyword'])) ? ($request['keyword']) : '';
        $this->sort = (!empty($request['sort'])) ? ($request['sort']) : 'desc';
        $this->perPage = (!empty($request['perPage'])) ? ($request['perPage']) : '15';
        $this->sortColumn = (!empty($request['sortType'])) ? ($request['sortType']) : 'insight_daily_us.id';
        // $this->limit = (!empty($request['limit'])) ? ($request['limit']) : 100;
        $or = false;
        $request = Helper::arrayUnset($request, ['sortType', "keyword", 'perPage', 'sort', 'page']);
        // get existing date list
        $dateList = $this->model
            ->select("insight_daily_us.date")
            ->groupBy("insight_daily_us.date")
            ->orderBy("insight_daily_us.date", "Desc")
            ->get();

        //get existing topic list
        $topicList = $this->model
            ->select("insight_daily_us.topic")
            ->groupBy("insight_daily_us.topic")
            ->get();

        foreach ($request as $field => $value) {
            if ($value instanceof \Closure) {
                $this->model = (!$or)
                    ? $this->model->where($value)
                    : $this->model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $this->model = (!$or)
                        ? $this->model->where($field, $operator, $search)
                        : $this->model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $this->model = (!$or)
                        ? $this->model->where($field, 'like', '%' . $value . '%')
                        : $this->model->orWhere($field, '=', $search);
                }
            } else {
                $this->model = (!$or)
                    ? $this->model->where($field, 'like', '%' . $value . '%')
                    : $this->model->orWhere($field, '=', $value);
            }
        }

        $result = $this->model;
        $resultArray = array();

        if (!empty($this->keyword)) {
            $result = $result->orwhere("insight_daily_us.headline", 'like', '%' . $this->keyword . '%');
            $result = $result->orwhere("insight_daily_us.full_story", 'like', '%' . $this->keyword . '%');
        }

        $resultArray['data'] = $result->orderBy($this->sortColumn, $this->sort)
            ->paginate($this->perPage);

        $resultArray['topics'] = $topicList;

        $resultArray['dates'] = $dateList;


        return $resultArray;
    }

    public function getTopicCategoryWithImages()
    {
        $topics = Config::get('custom_config.INSIGHT_DAILY_TOPIC_TYPES');
        $topicsToReturn = [];

        foreach ($topics as $topic){
            $topicsToReturn [] = [
                'name' => $topic['name'],
                'image' => url('/') . $topic['image']
            ];
        }

        return $topicsToReturn;
    }

    public function saveInsightDaily($data_array)
    {
        \Log::info($data_array);
        \Log::info(" -----------============================ ");
        $source = !empty($data_array['source_orgnization']) ? $data_array['source_orgnization'] : '';
        $headline = !empty($data_array['headline']) ? $data_array['headline'] : '';
        $full_story = !empty($data_array['full_story']) ? $data_array['full_story'] : '';
        //  $image_url = !empty($data_array['artical_image']) ? $data_array['artical_image'] : '';
        $topic = !empty($data_array['topic_type']) ? $data_array['topic_type'] : '';
        $source_url = !empty($data_array['artical_url']) ? $data_array['artical_url'] : '';
        $date = !empty($data_array['artical_date']) ? $data_array['artical_date'] : '';

        $objTopFive = new TopFive();
        $objTopFive->source = $source;
        $objTopFive->headline = $headline;
        $objTopFive->full_story = $full_story;
        $objTopFive->topic = $topic;
        $objTopFive->source_url = $source_url;
        $objTopFive->date = date('Y-m-d', strtotime($date));
        $objTopFive->save();

        return $this->uploadInsightDailyImg($data_array, 'POST', $objTopFive->id);
    }


    public function updateInsightDaily($data_array)
    {
        \Log::info($data_array);
        \Log::info(" &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& ");
        $source = !empty($data_array['source_orgnization']) ? $data_array['source_orgnization'] : '';
        $headline = !empty($data_array['headline']) ? $data_array['headline'] : '';
        $full_story = !empty($data_array['full_story']) ? $data_array['full_story'] : '';
        $topic = !empty($data_array['topic_type']) ? $data_array['topic_type'] : '';
        $source_url = !empty($data_array['artical_url']) ? $data_array['artical_url'] : '';
        $date = !empty($data_array['artical_date']) ? $data_array['artical_date'] : '';

        $this->model->where('id', '=', $data_array['id'])
            ->update(['source' => $source, 'headline' => $headline, 'full_story' => $full_story, 'topic' => $topic,
                'source_url' => $source_url, 'date' => date('Y-m-d', strtotime($date))]);

        if (!empty($data_array['artical_image'])) {
            //  if ( gettype ( $data_array[ 'artical_image' ] ) == 'object' ) {

            return $this->uploadInsightDailyImg($data_array, 'PUT', true);

            // }
            return true;

        } else {

            return true;
        }

    }


    public function uploadInsightDailyImg($data_array, $request = 'POST', $id = null, $flag = false)
    {
        $objTopFiveProfile = TopFive::find(isset($data_array['id']) ? $data_array['id'] : $id);

        if (!empty($data_array['artical_image'])) {

            // if ( gettype ( $data_array[ 'artical_image' ] ) == 'object' ) {

            $image = $data_array['artical_image'];
            $fileName = $objTopFiveProfile->id . "_" . uniqid() . '.' . 'png';;
            if ($request == 'PUT') {

                $existImgName = $objTopFiveProfile->image_url;
                $imageName = explode("/", $existImgName);

                if (!empty($imageName)) {

                    \File::delete(public_path(Config::get('custom_config.insight_daily_image_url')) . $objTopFiveProfile->date . "/"
                        . $imageName[(count($imageName) - 1)]);
                }

            }
            $destinationPath = (Config::get('custom_config.insight_daily_image_url')) . $objTopFiveProfile->date . "/";

            if (!file_exists(public_path(Config::get('custom_config.insight_daily_image_url')) . $objTopFiveProfile->date . "/")) {

                mkdir(public_path(Config::get('custom_config.insight_daily_image_url')) . $objTopFiveProfile->date . "/", 0777, true);
            }

            $this->imageBase64($image, $fileName, $destinationPath);
            //$data_array[ 'artical_image' ]->move ( $destinationPath, $fileName );
            $objTopFiveProfile->image_url = url('/') . $destinationPath . $fileName;

            if ($objTopFiveProfile->save()) {
                $flag = true;
            }
            //     }

        }


        return $flag;
    }


    public function imageBase64($image, $fileName, $destinationPath)
    {

        $data = $image;
        $image = str_replace('data:image/png;base64,', '', $data);
        $image = str_replace(' ', '+', $image);
        $imageName = $fileName;
        $file = Config::get('custom_config.insight_daily_image_url');
        \File::put(ltrim($destinationPath, '/') . $imageName, base64_decode($image));

    }

    public function insgihtDailyInfoById($id)
    {
        return $this->model
            ->select(
                [
                    "*"
                ])
            ->where(function ($query) use ($id) {
                if ($id) {

                    $query->where("id", $id);
                }
            })
            ->first();

    }

    public function deleteInsightDaily($request)
    {
        return $this->model->where('id', '=', $request['id'])
            ->delete();

    }


}