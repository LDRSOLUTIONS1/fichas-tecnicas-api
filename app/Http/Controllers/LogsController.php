<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        $logs = AccessLog::with([
            'user:id,first_name,middle_name,last_name,second_last_name,email',
        ])
            ->select(
                'id',
                'user_id',
                'token_id',
                'ip_address',
                'user_agent',
                'login_at',
                'logout_at',
            )
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($logs, 200);
    }
}
