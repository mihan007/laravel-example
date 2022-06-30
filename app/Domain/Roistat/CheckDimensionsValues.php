<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 05.05.2017
 * Time: 16:35.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

//todo: выпилить, не нужен
class CheckDimensionsValues
{
    /**
     * Check dimensions values.
     */
    public function check($specificCompany = null)
    {
        if ($specificCompany) {
            $companies = [$specificCompany];
        } else {
            $companies = Company::all();
        }

        foreach ($companies as $company) {
            $roistatConfig = $company->roistatConfig()->first();

            // it's not error, just there is no setting for roistat for this company
            if (empty($roistatConfig)) {
                continue;
            }

            // there is no error, just not set roistat settings
            if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
                continue;
            }

            $roistatDimensionsValues = $this->getRoistatDimensionsValues($roistatConfig);

            // it's error but we already logged error data
            if (false === $roistatDimensionsValues) {
                if (count($companies) === 1) {
                    return false;
                }
                continue;
            }

            $roistatDatabaseDimensionsValues = $roistatConfig->dimensionsValues()->get();

            if (empty($roistatDatabaseDimensionsValues)) {
                $roistatDatabaseDimensionsValues = [];
            } else {
                $roistatDatabaseDimensionsValues = $roistatDatabaseDimensionsValues->toArray();
            }

            $this->compareDimensionsValues($roistatConfig, $roistatDatabaseDimensionsValues, $roistatDimensionsValues);
        }

        return true;
    }

    protected function compareDimensionsValues($roistatConfig, $databaseDimensions, $respondDimensions)
    {
        if (empty($databaseDimensions)) {
            foreach ($respondDimensions as $key => $dimension) {
                $roistatConfig->dimensionsValues()->create(array_merge($dimension, ['is_active' => 0]));
            }

            return true;
        }

        foreach ($respondDimensions as $key => $dimension) {
            $isExists = $this->searchSameDimensionValue($dimension, $databaseDimensions);

            if (! $isExists) {
                $roistatConfig->dimensionsValues()->create(array_merge($dimension, ['is_active' => 0]));
            }
        }

        // delete database dimensions values if it's noe exists in respond
        foreach ($databaseDimensions as $key => $dimension) {
            $isExists = $this->searchSameDimensionValue($dimension, $respondDimensions);

            if (! $isExists) {
                $roistatConfig
                    ->dimensionsValues()
                    ->where('title', '=', $dimension['title'])
                    ->where('value', '=', $dimension['value'])
                    ->first()
                    ->delete();
            }
        }

        return true;
    }

    /**
     * Check if respond dimension exist in database.
     *
     * @param $respondDimension
     * @param $databaseDimensions
     * @return bool
     */
    protected function searchSameDimensionValue($respondDimension, $databaseDimensions)
    {
        foreach ($databaseDimensions as $key => $value) {
            if ($value['title'] === $respondDimension['title'] && $value['value'] === $respondDimension['value']) {
                return true;
            }
        }

        return false;
    }

    protected function getRoistatDimensionsValues($roistatConfig)
    {
        return $this->parseRespond(
            $roistatConfig,
            $this->roistatDimensionsValuesRequest(
                $roistatConfig->roistat_project_id,
                $roistatConfig->api_key
            )
        );
    }

    protected function parseRespond($roistatConfig, $respond)
    {
        if (empty($respond) || empty($respond['status'])) {
            Log::warning(
                'Roistat request response is empty',
                ['roistatConfig' => $roistatConfig->toArray(), 'response' => $respond]
            );

            return false;
        }

        if ($respond['status'] !== 'success') {
            Log::warning(
                'Roistat request is not successfuly finished',
                ['roistatConfig' => $roistatConfig->toArray(), 'response' => $respond]
            );

            return false;
        }

        if (empty($respond['values'])) {
            Log::warning(
                'Roistat request response is not valid',
                ['roistatConfig' => $roistatConfig->toArray(), 'response' => $respond]
            );

            return false;
        }

        return $respond['values'];
    }

    protected function roistatDimensionsValuesRequest($projectId, $key)
    {
        $query = "https://cloud.roistat.com/api/v1/project/analytics/dimension-values?project=$projectId&key=$key&is_new=1&dimension=marker_level_1";

        return Curl::to($query)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
