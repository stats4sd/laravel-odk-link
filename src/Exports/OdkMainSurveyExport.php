<?php


namespace Stats4sd\OdkLink\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Stats4sd\OdkLink\Exports\Traits\HandlesOdkSubmissions;
use Stats4sd\OdkLink\Models\Xlsform;

class OdkMainSurveyExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{

    public function __construct(public Collection $content, public Collection $keys)
    {
    }

    public function collection()
    {
        return $this->content->map(function ($entry) {

            $data = [];

            // ensure that returned collection has an entry for every key, even if the original submission did not;
            foreach ($this->keys as $key) {
                $data[$key] = $entry[$key] ?? null;
            }


            $data['form_version'] = $entry['__system']['formVersion'];
            $data['submitter_name'] = $entry['__system']['submitterName'];
            $data['submission_date'] = $entry['__system']['submissionDate'];
            return $data;
        });

    }

    public function headings(): array
    {
        return $this->keys
            ->merge([
                'form_version',
                'submitter_name',
                'submission_date',
            ])
            ->toArray();
    }


    public function title(): string
    {
        return 'main_survey';
    }
}
