<?php

namespace App\Cabinet\Company\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\GetCompanyAnalytic;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompanyAnalyticCalculatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index($accountId, $id, Request $request)
    {
        $company = Company::with('roistatConfig', 'roistatConfig.dimensionsValues')
            ->where('id', $id)
            ->firstOrFail();

        $date = Carbon::parse($request->get('date', 'yesterday'));

        $isEmptyRoistatConfig = empty($company->roistatConfig);
        $isEmptyRequest = $request === null;

        $info = [];

        $info['roistat_project_id'] = null;

        if (! $isEmptyRequest && $request->get('roistat_project_id')) {
            $info['roistat_project_id'] = $request->get('roistat_project_id');
        } elseif (! $isEmptyRoistatConfig && ! empty($company->roistatConfig->roistat_project_id)) {
            $info['roistat_project_id'] = $company->roistatConfig->roistat_project_id;
        }

        $info['api_key'] = null;

        if (! $isEmptyRequest && $request->get('api_key')) {
            $info['api_key'] = $request->get('api_key');
        } elseif (! $isEmptyRoistatConfig && ! empty($company->roistatConfig->api_key)) {
            $info['api_key'] = $company->roistatConfig->api_key;
        }

        $info['timezone'] = '+0200';

        if (! $isEmptyRequest && $request->get('timezone')) {
            $info['timezone'] = $request->get('timezone');
        } elseif (! $isEmptyRoistatConfig && ! empty($company->roistatConfig->timezone)) {
            $info['timezone'] = $company->roistatConfig->timezone;
        }

        $info['dimensionsValues'] = [];

        if (! $isEmptyRoistatConfig &&
            ! $isEmptyRequest &&
            $request->get('dimensionsValues') &&
            ! empty($company->roistatConfig->dimensionsValues)
        ) {
            $requestDimensions = $request->get('dimensionsValues');

            foreach ($company->roistatConfig->dimensionsValues as $dimensionsValue) {
                $info['dimensionsValues'][] = [
                    'title' => $dimensionsValue->title,
                    'value' => $dimensionsValue->value,
                    'is_active' => array_search($dimensionsValue->value, $requestDimensions) !== false,
                ];
            }
        } elseif (! $isEmptyRoistatConfig && ! empty($company->roistatConfig->dimensionsValues)) {
            foreach ($company->roistatConfig->dimensionsValues as $dimensionsValue) {
                $info['dimensionsValues'][] = [
                    'title' => $dimensionsValue->title,
                    'value' => $dimensionsValue->value,
                    'is_active' => $dimensionsValue->is_active ? true : false,
                ];
            }
        }

        $info['analytic'] = [
            'visitCount' => 0,
            'visits2leads' => 0,
            'leadCount' => 0,
            'visitsCost' => 0,
            'costPerClick' => 0,
            'costPerLead' => 0,
        ];

        if (! $isEmptyRoistatConfig && ! empty($info['roistat_project_id']) && ! empty($info['api_key'])) {
            $analytic = new GetCompanyAnalytic($info['roistat_project_id'], $info['api_key']);

            $formatBeginDate = $date->toDateString().'T00:00:00'.$info['timezone'];
            $formatEndDate = $date->toDateString().'T23:59:59'.$info['timezone'];

            $period['from'] = $formatBeginDate;
            $period['to'] = $formatEndDate;

            $activeDimensions = array_filter($info['dimensionsValues'], function ($value, $key) {
                return $value['is_active'];
            }, ARRAY_FILTER_USE_BOTH);

            $analyticData = $analytic->get($period, array_column($activeDimensions, 'value'));

            if ($analyticData !== false) {
                $info['analytic'] = $analyticData;
            }
        }

        $info['analytic']['for_date'] = $date->toDateString();

        return view('pages.company.analytic-calculator.index', ['company' => $company, 'info' => $info]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
