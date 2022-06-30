<?php

namespace App\Support\Replacement\File;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 06.06.2017
 * Time: 16:35.
 */
class ExcelParser
{
    /**
     * Excel file path.
     *
     * @var
     */
    private $filePath;

    /**
     * Excel reader.
     *
     * @var null|\PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $excelReader = null;

    /**
     * ExcelParser constructor.
     *
     * @param $pathToFile
     */
    public function __construct($pathToFile)
    {
        $this->filePath = $pathToFile;

        $this->excelReader = IOFactory::load(Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().$pathToFile);
    }

    /**
     * Get titles in file.
     *
     * @return array
     */
    public function getTitles()
    {
        $worksheet = $this->excelReader->getActiveSheet();
        $result = [];

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();

            foreach ($cellIterator as $cell) {
                $tempResult = $cell->getValue();

                if (! ($tempResult instanceof RichText)) {
                    $result[] = $tempResult;
                    continue;
                }

                $result[] = $tempResult->getPlainText();
            }

            if ($rowIndex > 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * Get readable content from file.
     *
     * @return array
     */
    public function getContent()
    {
        $worksheet = $this->excelReader->getActiveSheet();
        $result = [];
        $titles = $this->getTitles();
        $titlesSize = count($titles);

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            // we no need titles
            if (0 == $rowIndex || 1 == $rowIndex) {
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowContent = [];
            $columnIterator = -1; // It's comfortable to begin from -1 then 0

            foreach ($cellIterator as $cellIndex => $cell) {
                $columnIterator++;

                // read only cells that have title
                // we need it because cell can be empty
                if ($cellIndex >= $titlesSize) {
                    break;
                }

                // if keyword doesn't exist
                if ('keyword' == $titles[$columnIterator] && empty($cell->getValue())) {
                    break;
                }

                $rowContent[$titles[$columnIterator]] = empty($cell->getValue()) ? '' : $cell->getValue();
            }

            if (! empty($rowContent) && ! empty($rowContent['keyword'])) {
                $result[] = $rowContent;
            }
        }

        return $result;
    }
}
