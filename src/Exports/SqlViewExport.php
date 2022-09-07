<?php


namespace Stats4sd\OdkLink\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SqlViewExport implements \Maatwebsite\Excel\Concerns\FromCollection, WithHeadings
{
    public string $viewName;
    public mixed $owner;

    public function __construct(string $viewName, $owner = null)
    {
        $this->viewName = $viewName;
        $this->owner = $owner;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        if ($this->owner) {

            // filter query to only return items linked to the given owner
            $collection = DB::table($this->viewName)
                ->where('owner_id', '=', $this->owner->id)
                ->where('owner_type', '=', get_class($this->owner))
                ->get();

            // unset the owner identifier variables
            return $collection->map(function ($item) {
                unset($item->owner_id);
                unset($item->owner_type);

                return $item;
            });
        }

        return DB::table($this->viewName)->get();
    }

    public function headings(): array
    {
        $example = DB::table($this->viewName)->limit(1)->get();

        return collect($example->first())
            ->keys()
            ->filter(function ($heading) {
                return $heading !== "owner_id" && $heading !== "owner_type";
            })->toArray();
    }
}
