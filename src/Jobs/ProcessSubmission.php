<?php

namespace Stats4sd\OdkLink\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Stats4sd\OdkLink\Imports\XlsformImport;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\Xlsform;


/**
 * This job opens up the actual XLS file for a given Xlsform and updates the form_id and form_title fields.
 * This should be done before the file is uploaded to the ODK Aggregation service.
 */
class ProcessSubmission implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Submission $submission)
    {
    }


    /**
     * @throws Exception
     */
    public function handle(): void
    {

        // Get the XLSform details to read the data map;
        $xlsformVersion = $this->submission->xlsformVersion;
        $xlsformFile = Excel::toCollection(new XlsformImport, Storage::disk(config('kobo-link.xlsforms.storage_disk'))->path($xlsformVersion->xlsfile));

        $survey = $xlsformFile['survey'];

        $survey = $this->addRepeatGroupPathToEachField($survey);

        // get fields for processing into db tables
        $allFields = $survey->filter(fn($item) => $item['db_table'] !== null);

        // what tables are going to be updated?
        $tables = $allFields->pluck('db_table')->unique();
        dump($tables);

//        dd($this->submission->content);

        foreach ($tables as $table) {
            $fields = $allFields->filter(fn($field) => $field['db_table'] === $table);

            // by default, a single entry will be created, using the "top" level of the ODK form submission.
            // if fields inside a repeat group are included, some sort of error should be thrown.

            // to create entries at a lower level, the user should specify the db_table name on the repeat group.
            if ($level = $fields->filter(fn($field) => $field['type'] === "begin_repeat" || $field['type'] === "begin repeat")->first()) {

                // get the 'main data' - the contents of the repeat group:
                $mainData = collect($this->submission->content[$level['name']]);

                // get all the fieldnames for processing
                $allFieldNames = $fields->filter(fn($field) => $field['type'] !== "begin_repeat" && $field['type'] !== "begin repeat")->pluck('name');

                // get all the field names from the mainData:
                $mainDataKeys = $mainData->reduce(function ($carry, $field) {
                    return $carry->merge(collect($field)->keys());
                }, collect([]))?->unique();

                // get the difference. These are the remaining fields to find *outside* of the mainData repeatgroup.
                $remainingFieldNames = $allFieldNames->diff($mainDataKeys);

                $mainData = $mainData->map(function($entry) use ($remainingFieldNames) {

                    // this only works for top-level fields. No Nested repeat groups yet!!!
                    foreach ($remainingFieldNames as $fieldName) {
                        $entry[$fieldName] = $this->submission->content[$fieldName];
                    }

                    return $entry;
                });

                // handle different data types

                // special handling for GPS;
                // special handling for images (also download from ODK and store locally?)
                // optional handling for select multiples to split into booleans?
            }
        }


    }

    private function addRepeatGroupPathToEachField(Collection $survey): Collection
    {

        $repeatLevel = [];

        for ($i = 0, $iMax = $survey->count(); $i < $iMax; $i++) {

            if ($survey[$i]['type'] === "begin_repeat" || $survey[$i]['type'] === "begin repeat") {
                $repeatLevel[] = $survey[$i]['name'];
            }

            if ($survey[$i]['type'] === "end_repeat" || $survey[$i]['type'] === "end repeat") {
                array_pop($repeatLevel);
            }

            $survey[$i]['repeat_level'] = implode("/", $repeatLevel);
        }
        return $survey;
    }
}
