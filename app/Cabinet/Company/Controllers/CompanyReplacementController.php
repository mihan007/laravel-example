<?php

namespace App\Cabinet\Company\Controllers;

use App\Cabinet\Company\Requests\ReplacementSynchronizationRequest;
use App\Domain\Beget\DatabaseApi;
use App\Domain\Company\Models\Company;
use App\ReplacementReplace;
use App\Support\Replacement\File\ExcelParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompanyReplacementController extends \App\Support\Controllers\Controller
{
    public function index($id)
    {
        $company = Company::with(['replacementDatabaseConfigs'])->find($id);

        $respond = [];

        $respond['company'] = $company;
        $respond['replacement'] = [];

        foreach ($company->replacementDatabaseConfigs as $config) {
            $respond['replacement'][] = [
                'id' => $config->id,
                'description' => $config->comment,
                'database_name' => $config->name,
                'login' => $config->login,
                'password' => $config->password,
                'created_at' => $config->created_at->toDateString(),
            ];
        }

        return view('pages.companies-replacement-list')->with($respond);
    }

    public function create($id)
    {
        $company = Company::with(['replacementDatabaseConfigs'])->find($id);

        $respond = [];

        $respond['company'] = $company;
        $respond['replacement'] = [];

        foreach ($company->replacementDatabaseConfigs as $config) {
            $respond['replacement'][] = [
                'id' => $config->id,
                'description' => $config->comment,
                'database_name' => $config->name,
                'login' => $config->login,
                'password' => $config->password,
                'created_at' => $config->created_at->toDateString(),
            ];
        }

        return view('pages.companies-replacement-edit')->with($respond);
    }

    /**
     * Create replacement database config for certain company.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store($id, Request $request)
    {
        $company = Company::with(['replacementDatabaseConfigs'])->find($id);

        // if company database config is already exists
        if (! empty($company->replacementDatabaseConfigs)) {
            Log::error('Couldn\'t create replacement database config. Config is already exists', $company->toArray());

            return redirect()->route('account.replacement.index', ['id' => $company->id]);
        }

        $databaseApi = new DatabaseApi();

        $requestArray = $request->toArray();

        foreach ($requestArray as $database) {
            if ($database['id'] !== 'new') {
                continue;
            }

            $begetDatabaseListResponse = $databaseApi->getList();

            if ('success' !== $begetDatabaseListResponse['status'] || 'success' !== $begetDatabaseListResponse['answer']['status']) {
                Log::error('Couldn\'t get list of databases from beget', [$company->toArray(), $begetDatabaseListResponse]);

                return redirect()->route('account.replacement.index', ['id' => $company->id]);
            }

            $databaseSuffix = $this->getUniqueDatabaseSuffix(array_column($begetDatabaseListResponse['answer']['result'], 'name'));
            $databasePassword = Str::random(16);

            $begetAddDatabaseStatus = $databaseApi->addDb($databaseSuffix, $databasePassword);

            if ('success' !== $begetAddDatabaseStatus['status'] || 'success' !== $begetAddDatabaseStatus['answer']['status']) {
                Log::error('Couldn\'t add new database in beget', [$company->toArray(), $begetAddDatabaseStatus]);

                return redirect()->route('account.replacement.index', ['id' => $company->id]);
            }

            $begetUsername = Config::get('beget.username');

            $replacement = $company->replacementDatabaseConfigs()->create([
                'name' => $begetUsername.'_'.$databaseSuffix,
                'login' => $begetUsername.'_'.$databaseSuffix,
                'password' => $databasePassword,
                'comment' => ! empty($database['comment']) ? $database['comment'] : 'Подмена заголовков для '.$company->name,
            ]);

            ReplacementReplace::init($company->id, $replacement->id);

            $replacementTable = new ReplacementReplace();

            $replacementTable->migrate();
        }

        $company = Company::with(['replacementDatabaseConfigs'])->find($id);

        return redirect()->route('account.replacement.index', ['id' => $company->id]);
    }

    public function edit($id, $replacementId)
    {
        $company = Company::with(['replacementDatabaseConfigs'])->findOrFail($id);

        if (! empty($company->replacementDatabaseConfig)) {
            ReplacementReplace::init($company->id, $replacementId);

            $replacement = new ReplacementReplace();

            $replacementItems = ReplacementReplace::all();
            $replacementColumns = array_diff($replacement->getColumns(), ['id']);
        } else {
            $replacementItems = [];
            $replacementColumns = [];
        }

        return view('pages.companies-replacement', ['company' => $company, 'columns' => $replacementColumns, 'items' => $replacementItems]);
    }

    public function update($id, ReplacementSynchronizationRequest $request)
    {
        $company = Company::with(['replacementDatabaseConfig'])->findOrFail($id);

        ReplacementReplace::init($company->id);

        $replacementTable = new ReplacementReplace();

        $replacementColumns = $replacementTable->getColumns();

        $path = $request->file('file')->store('replacement');

        $excel = new ExcelParser($path);

        $excelColumns = $excel->getTitles();

        $appendColumns = array_diff($excelColumns, $replacementColumns);
        $replacementTable->addColumns($appendColumns);

        $removeColumns = array_diff($replacementColumns, $excelColumns);
        $replacementTable->removeColumns($removeColumns);

        $databaseReplacements = $replacementTable->all();
        $excelReplacements = $excel->getContent();

        $this->syncReplacementData(
            $excelColumns,
            $excelReplacements,
            $databaseReplacements
        );

        return redirect()->route('account.replacement.index', ['id' => $company->id]);
    }

    protected function syncReplacementData($columns, $excelData, $databaseData)
    {
        $excelKeywords = array_column($excelData, 'keyword');
        $removeDatabaseRowsCollection = collect();
        $updateDatabaseRowsCollection = collect();
        $excelAttachIndexes = [];

        // Для каждой строки в базе данных
        foreach ($databaseData as $databaseLoopIndex => $databaseRow) {
            // Ищем соответствующую строку с такой же ключевой фразой в файле
            $excelSearchIndex = array_search($databaseRow->keyword, $excelKeywords);

            // Если не нашли
            if (false === $excelSearchIndex) {
                // Добавляем в данные для удаления
                $removeDatabaseRowsCollection->push($databaseRow);

                // Далее
                continue;
            }

            // Добавляем индекс найденной ключевой фразе
            $excelAttachIndexes[] = $excelSearchIndex;

            $isDifferent = false;

            // Для каждого столбца(кроме id и кючевой фразы) из файла
            // Сравноивем значение в файле со значением в базе
            foreach (array_diff($columns, ['keyword']) as $columnIndex => $column) {
                // Если различны
                if ($databaseRow[$column] != $excelData[$excelSearchIndex][$column]) {
                    $isDifferent = true;

                    // Добавляем различия
                    $databaseRow[$column] = $excelData[$excelSearchIndex][$column];
                }
            }

            if (! $isDifferent) {
                continue;
            }

            $updateDatabaseRowsCollection->push($databaseRow);
        }

        // Удаляем несуществующие ключевые фразы
        $removeDatabaseRowsCollection->each(function ($item, $key) {
            $item->delete();
        });

        // Обновляем данные существующих ключевых фразе
        $updateDatabaseRowsCollection->each(function ($item, $key) {
            $item->save();
        });

        // Ищем строки, индексы которых не совпадают с индексами найденых ключевых фразе
        // Добавляем новые ключевые фразы в базу данных
        foreach ($excelData as $rowIndex => $excelRow) {
            if (in_array($rowIndex, $excelAttachIndexes)) {
                continue;
            }

            (new ReplacementReplace())->create($excelRow);
        }

        return true;
    }

    /**
     * Get unique database suffix for beget.
     *
     * @param $listOfBegetDatabases
     * @return string
     */
    protected function getUniqueDatabaseSuffix($listOfBegetDatabases)
    {
        $username = Config::get('beget.username');

        $suffixLimit = 16 - (strlen($username) + 1);

        $databaseSuffix = strtolower(Str::random($suffixLimit));

        while (in_array($username.'_'.$databaseSuffix, $listOfBegetDatabases)) {
            $databaseSuffix = strtolower(Str::random($suffixLimit));
        }

        return $databaseSuffix;
    }
}
