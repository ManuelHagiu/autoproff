<?php

namespace Tests\Feature;

use App\Imports\UsersImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_api_is_accessible()
    {
        $this->json('post', '/api/import-csv')
            ->assertStatus(200);
    }

    /**
     * Feature test for the csv import file
     */
    public function test_import_csv_file()
    {
        Storage::fake('local');

        $fileName = 'test.csv';
        $file = UploadedFile::fake()->create($fileName);

        $import = new UsersImport();
        $path = $import->uploadCsv($file, $fileName);

        //Assert that the returned response from import is a string (path)
        $this->assertIsString($path);

        //Assert the file was stored
        Storage::disk('local')->assertExists($path);

        //Delete the file after test
        Storage::delete($path);

        //Check if the file is deleted
        Storage::assertMissing($path);

    }

    /**
     * Feature test for data extraction
     */
    public function test_extract_data_from_csv()
    {
        $path = base_path('/tests/data/test.csv');

        $import = new UsersImport();
        $data = $import->extractData($path);

        //Assert that the returned response from import is array
        $this->assertIsArray($data);
    }

    /**
     * Feature test to return the duplicates from the csv file if present.
     */
    public function test_find_duplicate_ages()
    {
        //Preconditions
        //Mocking data
        $data = [
            [
                'name' => 'Cristian',
                'age' => 27
            ],
            [
                'name' => 'Alex',
                'age' => 30
            ],
            [
                'name' => 'Omar',
                'age' => 27
            ],
            [
                'name' => 'Alexa',
                'age' => 31
            ]
        ];

        //Operations
        $import = new UsersImport();
        $duplicated = $import->duplicateWithPercentage($data, 'age');

        //Assertions
        $this->assertArrayHasKey(27, $duplicated);
        $this->assertEquals(round(50, 2), $duplicated[27]);
    }

    /**
     * Full test upload and extract
     */
    public function test_api_response()
    {
        //File for the api call
        $file = new UploadedFile(base_path('/tests/data/test.csv'), 'test.csv');

        //Call to the api
        $response = $this->postJson('/api/import-csv', ['file' => $file]);

        //Assertions
        $response->assertJson([
            27 => 20,
            33 => 20,
            23 => 20,
        ])->assertStatus(200);

    }


}
