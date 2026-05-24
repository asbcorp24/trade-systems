<?php

namespace App\Http\Controllers;

use App\Models\PriceHistory;

class PriceHistoryController extends Controller
{
    public function index()
    {
        $rows = PriceHistory::with('product')->orderByDesc('id')->paginate(50);

        return view('prices.index', compact('rows'));
    }
}
