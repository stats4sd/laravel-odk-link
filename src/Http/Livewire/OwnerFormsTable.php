<?php

namespace Stats4sd\OdkLink\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Xlsform;

class OwnerFormsTable extends DataTableComponent
{

    public OdkProject $odkProject;

    public function builder(): Builder
    {
        return Xlsform::query()
            ->whereHas('owner', function ($query) {
                $query->whereHas('odkProject', function ($query) {
                    $query->where('odk_projects.id', $this->odkProject->id);
                });
            })
            ->select('title', 'xlsfile', 'has_draft', 'is_active', 'odk_id');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setColumnSelectStatus(false);
    }

    public function columns(): array
    {
        return [
            Column::make('title')
                ->sortable()
                ->searchable(),
            LinkColumn::make('xlsfile')
                ->title(fn($row) => $row->xlsfile)
                ->location(fn($row) => Storage::disk(config('odk-link.storage.xlsforms'))->url($row->xlsfile)),
            Column::make('is_active'),
            Column::make('has_draft'),
        ];
    }
}
