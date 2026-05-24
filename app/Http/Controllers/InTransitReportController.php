<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;

class InTransitReportController extends Controller
{
    public function index()
    {
        $rows = StockTransfer::with(['fromWarehouse', 'fromStore', 'toWarehouse', 'toStore', 'items.product'])
            ->whereIn('status', ['shipped', 'partially_received'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('reports.in_transit', compact('rows'));
    }
}
