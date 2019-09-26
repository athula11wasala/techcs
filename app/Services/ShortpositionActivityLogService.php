<?php

namespace App\Services;

use App\Repositories\ShortpositionActivityLogRepository;
use Illuminate\Support\Facades\Auth;
use App\Equio\Helper;
use Illuminate\Support\Facades\Config;

class ShortpositionActivityLogService {

    private $shortpositionActivityLogRepository;
        
    /**
     * ShortpositionActivityLogService constructor.
     * @param ShortpositionActivityLogRepository $shortpositionActivityLogRepository
     */
    public function __construct(
        ShortpositionActivityLogRepository $shortpositionActivityLogRepository
    ) {
        $this->shortpositionActivityLogRepository = $shortpositionActivityLogRepository;
               
    }

    /**
     * handle custom error message
     * @param $exception
     * @return mixed
     */
    public function errorMessage($exception) {
        $error['message'] = !empty($exception) ? $exception : '';
        $error['code'] = '400';
        return $error;
    }

    /**
     * Store log
     * @param array $data
     * @return array response
     */
    public function storeShortpositionActivityLog($data = array()) 
    {
        return $this->shortpositionActivityLogRepository
            ->storeShortpositionActivityLog($data);
    }

    /**
     * Retrieve log
     */
    public function getShortpositionActivityLog($userId = '') 
    {
        return $this->shortpositionActivityLogRepository
            ->getShortpositionActivityLog($userId);
    }

    /**
     * Retrieve latest Donwgrade log
     */
    public function getLatestDownGradeActivityInfo($userId = '',$action='',$downGradeTrigger='')
    {

        return $this->shortpositionActivityLogRepository
            ->getLatestDownGradeActivityLog($userId, $action,$downGradeTrigger);
    }

}
