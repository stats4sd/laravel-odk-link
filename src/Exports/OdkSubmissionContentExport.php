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
    public array $sheets;

    public function __construct(public Xlsform $xlsform)
    {
        $this->content = $this->extractContent($xlsform);
        [$this->keys, $this->repeats] = $this->extractKeysAndRepeats($this->content);


    }

    public function sheets(): array
    {
        $this->sheets = [
            new OdkMainSurveyExport($this->content, $this->keys),
        ];

        $this->handleRepeats($this->content, $this->repeats, 'main_survey');

        return $this->sheets;
    }

    private function extractContent(Xlsform $xlsform): Collection
    {
        return $xlsform->submissions->pluck('content')->map(fn($entry) => $this->processGps($entry));
    }

    private function extractRepeatContent(Collection $content, string $repeat, string $parent): Collection
    {
        return $content->pluck($repeat, '__id')
            ->flatMap(function ($entries, $id) use ($parent) {

                return collect($entries)->map(function ($entry) use ($id, $parent) {
                    $entry['__parent_id'] = $id;
                    $entry['__parent_table'] = $parent;
                    return $entry;
                });
            })->map(fn($entry) => $this->processGps($entry));
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

    private function handleRepeats(Collection $content, Collection $repeats, string $parent): void
    {
        foreach ($repeats as $repeatName) {
            $repeatContent = $this->extractRepeatContent($content, $repeatName, $parent);
            [$repeatKeys, $repeatRepeats] = $this->extractKeysAndRepeats($repeatContent);

            $this->sheets[] = new OdkRepeatExport($repeatContent, $repeatKeys, $repeatName);

            $this->handleRepeats($repeatContent, $repeatRepeats, $repeatName);
        }

    }

    private function processGps($entry)
    {
        $gpsKeys = collect([]);
        foreach ($entry as $key => $value) {
            if (is_array($value) && isset($value['type']) && $value['type'] === "Point") {

                $gpsKeys = $gpsKeys->merge($key);

                $entry["{$key}_longitude"] = $value['coordinates'][0];
                $entry["{$key}_latitude"] = $value['coordinates'][1];
                $entry["{$key}_altitude"] = $value['coordinates'][2];
                $entry["{$key}_accuracy"] = $value['properties']['accuracy'];

                unset($entry[$key]);
            }
        }

        return $entry;
    }

}
