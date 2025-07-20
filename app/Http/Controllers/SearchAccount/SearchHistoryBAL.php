<?php

namespace App\Http\Controllers\SearchAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DAL\SearchHistoryDAL;

class SearchHistoryBAL extends Controller
{
    protected $searchHistoryDAL;

    public function __construct(SearchHistoryDAL $searchHistoryDAL)
    {
        $this->searchHistoryDAL = $searchHistoryDAL;
    }
    public function saveSearchHistory(Request $request)
    {
        $data = $request->only(['viewer_code', 'viewed_code', 'activity_type']);

        return $this->searchHistoryDAL->saveSearchHistory($data);
    }
}
