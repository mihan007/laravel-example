<?php

namespace App\Cabinet\Dashboard\Controllers;

use App\Domain\ProxyLead\Repositories\LeadgenChartRepository;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class LeadgenChartController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'channel_id' => 'nullable|numeric|min:1|exists:channels,id',
            'type' => 'nullable|in:day,month',
        ]);

        return (new LeadgenChartRepository($request))->get();
    }
}
