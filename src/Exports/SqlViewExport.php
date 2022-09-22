<?php


namespace Stats4sd\OdkLink\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SqlViewExport implements \Maatwebsite\Excel\Concerns\FromCollection, WithHeadings
{
    public function __construct(public string $viewName, public mixed $owner = null, public ?string $ownerForeignKey = null)
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        if ($this->owner) {

            // filter query to only return items linked to the given owner
            $query = DB::table($this->viewName);

            if ($this->ownerForeignKey) {
                $query = $query->where($this->ownerForeignKey, '=', $this->owner->id);
            } else {
                $query = $query
                    ->where('owner_id', '=', $this->owner->id)
                    ->where('owner_type', '=', get_class($this->owner));
            }

            // unset the owner identifier variables
            return $query->get()->map(function ($item) {
                $foreignKey = $this->ownerForeignKey;
                unset($item->owner_id, $item->owner_type, $item->$foreignKey);

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
                return $heading !== "owner_id" && $heading !== "owner_type" && $heading !== $this->ownerForeignKey;
            })->toArray();
    }
}
