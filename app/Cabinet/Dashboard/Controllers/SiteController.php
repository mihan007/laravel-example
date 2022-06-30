<?php

namespace App\Cabinet\Dashboard\Controllers;

use App\Domain\Company\Models\Company;
use App\Support\Controllers\Controller;
use Carbon\Carbon;

class SiteController extends Controller
{
    public function index($id)
    {
        $company = Company::with('sites')->findOrFail($id);

        $data['company'] = [
            'name' => $company->name,
        ];

        $data['sites'] = [];

        if (! empty($company->sites)) {
            foreach ($company->sites as $site) {
                $data['sites'][] = [
                    'url' => $site->url,
                    'mobile_score' => $site->mobile_score,
                    'mobile_usability' => $site->mobile_usability,
                    'desktop_score' => $site->desktop_score,
                    'last_pagespeed_sync' => Carbon::parse($site->last_pagespeed_sync)->toDateString(),
                ];
            }
        }

        return view('pages.company.sites.index', ['data' => $data]);
    }
}
