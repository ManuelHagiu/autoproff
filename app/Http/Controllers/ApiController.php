<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    // TODO: add parameter validations
    /**
     * Import api to for all the ages that were duplicated and the percentage of rows that had the same age
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function import(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                //Import CSV file
                $import = new UsersImport();
                $file = $request->file('file');
                $path = $import->uploadCsv($file);

                //Extract data from CSV
                $data = $import->extractData($path);

                //Find duplicates from data
                $duplicates = $import->duplicateWithPercentage($data, 'age');

                return response()->json($duplicates);

            }
        } catch (\Exception $exception) {
            // TODO: log exception and return message
        }

        return response()->json(['success' => false, 'message' => 'File upload failed.']);
    }

}
