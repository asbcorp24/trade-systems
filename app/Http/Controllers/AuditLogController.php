<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::orderByDesc('id')->paginate(50);

        return view('audit.index', compact('logs'));
    }
}
