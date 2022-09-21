<?php


namespace Stats4sd\OdkLink\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Stats4sd\OdkLink\Exports\Traits\HandlesOdkSubmissions;
use Stats4sd\OdkLink\Models\Xlsform;

class OdkRepeatExport implements FromCollection, WithHeadings, WithTitle
{
    use HandlesOdkSubmissions;

    public function __construct(public Collection $content, public Collection $keys, public string $repeatName)
    {
    }

    public function collection()
    {
        return $this->content->map(function($entry) {
            return collect($entry)->only($this->keys)->toArray();
        });
    }

    public function headings(): array
    {
        return $this->keys->toArray();
    }

    public function title(): string
    {
        return $this->repeatName;
    }
}
