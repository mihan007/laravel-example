<?php


namespace App\Support\Helper;

use App\Domain\Company\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RouteHelper
{
    public static function getCompanyFromRoute(Request $request)
    {
        $companyId = null;

        if (is_object($request->route('company'))) {
            $companyId = $request->route('company')->id;
        } elseif ($request->route('publicId')) {
            $company = Company::where('public_id', $request->route('publicId'))->firstOrFail();
            $companyId = $company->id;
        } else {
            $companyId = $request->route('id') ?? $request->route('company');
        }

        return Company::findOrFail($companyId);
    }

    public static function getDateRange(Request $request)
    {
        $startAt = $request->has('start_at') && !empty($request->get('start_at'))
            ? Carbon::parse($request->get('start_at'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $endAt = $request->has('end_at') && !empty($request->get('end_at'))
            ? Carbon::parse($request->get('end_at'))->endOfDay()
            : now()->endOfDay()->endOfDay();

        return [$startAt, $endAt];
    }
}
