<?php

namespace App\Cabinet\Support\Controllers;

use App\Support\Controllers\Controller;
use App\Support\Models\ScheduleTaskLog;
use Illuminate\Http\Request;

class SchedulePingController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'type' => 'required|in:start,finish',
            'name' => 'required',
        ]);

        $searchData = [
            'task_id' => $request->get('id'),
            'name' => $request->get('name'),
        ];

        if ('start' === $request->get('type')) {
            $inputData = ['started_at' => now()->toDateTimeString()];
        } else {
            $inputData = ['finished_at' => now()->toDateTimeString()];
        }

        ScheduleTaskLog::updateOrCreate($searchData, $inputData);
    }
}
