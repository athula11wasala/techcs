<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends ApiController
{
    private $newsService;

    public function __construct(NewsService $newsService) {
        $this->newsService = $newsService;
    }

    public function getIndexNews(Request $request) {
        \Log::info("==== NewsController->getIndexNews ", ['u' => json_encode($request)]);
        $index = $request->index;
        $news = $this->newsService->getIndexNews($index);
        $response = response()->json(['news' => $news], 200);
        return $response;
    }

    public function getCompanyNews(Request $request) {
        \Log::info("==== NewsController->getCompanyNews ", ['u' => json_encode($request)]);
        $index = $request->index;
        $news = $this->newsService->getCompanyNews($index);
        $response = response()->json(['news' => $news], 200);
        return $response;
    }

    public function getCompanySectorNews(Request $request) {
        \Log::info("==== NewsController->getCompanySectorNews ", ['u' => json_encode($request)]);
        $index = $request->index;
        $news = $this->newsService->getCompanySectorNews($index);
        $response = response()->json(['news' => $news], 200);
        return $response;
    }

}
