<?php


namespace Stats4sd\OdkLink\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Stats4sd\OdkLink\Exports\Traits\HandlesOdkSubmissions;
use Stats4sd\OdkLink\Models\Xlsform;

class OdkSubmissionContentExport implements WithMultipleSheets
{
    public Collection $keys;
    public Collection $content;
    public Collection $repeats;

    public function __construct(public Xlsform $xlsform)
    {
        $this->content = $this->extractContent($xlsform);
        [$this->keys, $this->repeats] = $this->extractKeysAndRepeats($this->content);
    }

    public function sheets(): array
    {
        $sheets = [
            new OdkMainSurveyExport($this->content, $this->keys),
        ];

        foreach ($this->repeats as $repeatName) {
            $sheets[] = new OdkRepeatExport($this->content, $repeatName);
        }

        return $sheets;
    }

    private function extractContent(Xlsform $xlsform): Collection
    {
        return $xlsform->submissions->pluck('content');
    }

    private function extractKeysAndRepeats(Collection $content): array
    {

        // collect all keys from all submissions to get the full set of
        $keys = $content->reduce(function ($result, $entry) {
            return $result->concat(collect($entry)->keys())->unique();
        }, collect([]));

        $repeats = $keys
            ->filter(fn($key) => Str::contains($key, '@odata.navigationLink'))
            ->map(fn($key) => Str::replace('@odata.navigationLink', '', $key));

        //

        // filter out unwanted keys
        $keys = $keys->filter(function ($key) use ($repeats) {
            return collect(['meta', '__system'])->doesntContain($key)
                && !Str::contains($key, '@odata.navigationLink')
                && $repeats->doesntContain($key);
        });

        return [$keys, $repeats];
    }
}
