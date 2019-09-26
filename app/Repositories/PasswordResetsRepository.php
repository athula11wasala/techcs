<?php

namespace App\Repositories;
use Bosnadev\Repositories\Eloquent\Repository;

class PasswordResetsRepository extends Repository
{
    public function model()
    {
        return 'App\Models\PasswordResets';
    }

    public function getToken($token)
    {
        return $this->model->where('token', '=', $token)->first();
    }
}