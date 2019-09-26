<?php

namespace App\Services;

use App\DasetFactory\DatasetFactoryMethod;
use App\Equio\Exceptions\EquioException;
use App\Repositories\KeywordsRepository;
use DB;
use Illuminate\Support\Facades\Config;

class KeyWordService
{


    private $keyWordRepository;

    /**
     * topFiveService constructor.
     * @param KeyWord $keyWordRepository
     */
    public function __construct(
        KeywordsRepository $keyWordRepository

    )
    {

        $this->keyWordRepository = $keyWordRepository;

    }

    public function createKeyWord($keyWordArr)
    {
        return $this->keyWordRepository->saveKeyWord ( $keyWordArr );
    }

    public function getUpdateKeyWord($keyWordArr)
    {
        return $this->keyWordRepository->updateKeyWord ( $keyWordArr );
    }

    public function deleteKeyWord($id)
    {
        return $this->keyWordRepository->deleteKeyWord ( $id );
    }

    public function getAllKeyWordDetailPaginate($request)
    {
        return $this->keyWordRepository->allKeyWordDetailPaginate ( $request->all () );
    }

    public function getAllKeyWordDetail($request)
    {
        return $this->keyWordRepository->KeyWordDetail ( $request->all () );
    }

}
