<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class UsersImport implements WithHeadingRow
{
    /**
     * @param UploadedFile $file
     * @param string $fileName
     * @return string
     */
    public function uploadCsv(UploadedFile $file, string $fileName = ''): string
    {
        if ($file instanceof UploadedFile) {
            //Set filename
            $fileName = (!empty($fileName)) ? $fileName : now()->timestamp . '_' . $file->getClientOriginalName();

            //Save file and return the path
            return $file->storeAs('imports', $fileName);
        } else {
            //Handle exception with possible message error
            //this could be avoided if validation are used
            return '';
        }

    }

    /**
     * @param string $filePath
     * @return array
     */
    public function extractData(string $filePath = ''): array
    {
        if (empty($filePath))
            return [];

        $array = Excel::toArray($this, $filePath);

        return current($array);
    }


    /**
     * @param array $data
     * @param string $column
     * @return array
     */
    public function duplicateWithPercentage(array $data = [], string $column = ''): array
    {
        //Get array with values by column
        $arrayColumn = array_column($data, $column);

        //Count and filter the number of duplicates
        $duplicates = array_filter(array_count_values($arrayColumn), function ($column) {
            return $column > 1;
        });

        //Calculate the percentage of duplicated
        return array_map(function ($value) use ($arrayColumn) {
            return round(($value / count($arrayColumn) * 100), 2);
        }, $duplicates);
    }

}
