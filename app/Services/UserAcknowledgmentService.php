<?php

namespace App\Services;

use App\Repositories\UserAcknowledgementRepository;
use App\Repositories\Criteria\Users\OrderByCreated;
use Join;

class UserAcknowledgmentService
{

    private $userAcknowledgementRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserAcknowledgementRepository $userAcknowledgementRepository)
    {
        $this->userAcknowledgementRepository = $userAcknowledgementRepository;
    }

    public function createUserAcknowledgements($array)
    {
        return $this->userAcknowledgementRepository->saveUserAcknowledgements ( $array );
    }


}


