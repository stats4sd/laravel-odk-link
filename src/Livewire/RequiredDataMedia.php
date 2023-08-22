<?php

namespace Stats4sd\OdkLink\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stats4sd\OdkLink\Models\Dataset;
use Stats4sd\OdkLink\Models\RequiredMedia;
use Symfony\Contracts\Service\Attribute\Required;

class RequiredDataMedia extends Component
{
    use WithFileUploads;

    public RequiredMedia $requiredMedia;
    public bool $isStaticFile;
    public ?Dataset $dataset;
    public string $datasetName;
    public string $datasetId;
    public string $datasetModel;

    public Collection $datasets;


    public function mount(RequiredMedia $requiredMedia)
    {
        $this->datasets = Dataset::select('id', 'name', 'entity_model')->get();
        $this->isStaticFile = $this->requiredMedia->is_static;
        $this->requiredMedia = $requiredMedia;
        $this->dataset = $requiredMedia->dataset;
        $this->datasetId = $this->dataset?->id ?? '';
        $this->datasetName = $this->dataset?->name ?? '';
        $this->datasetModel = $this->dataset?->entity_model ?? '';

        $this->dataset->load('variables');
    }


    public function pickDataset()
    {
        $this->dataset = Dataset::find($this->datasetId);
        $this->requiredMedia->attachment()->associate($this->dataset);
        $this->requiredMedia->save();

    }

    // changes Required Media type from static (uploaded file) to dynamic (links to dataset in the database)
    public function updateType()
    {
        $this->requiredMedia->update([
            'is_static' => $this->isStaticFile,
        ]);

        if ($this->isStaticFile) {
            $this->dataset = is_a($this->requiredMedia->attachment, Dataset::class) ? $this->requiredMedia->attachment : '';
        }

        $this->requiredMedia->refresh();
    }


    // show dataset creation modal
    public function createDataset()
    {
        $this->showDatasetModal = true;
    }

    public function storeDatasset()
    {
        $this->dataset = Dataset::create([
            'name' => $this->datasetName,
            'entity_model' => $this->datasetModel,
        ]);

        $this->requiredMedia->attachment->associate($this->dataset);

        $this->dataset->refresh();
        $this->requiredMedia->refresh();
    }

    public function addVariable()
    {
        $this->dataset->variables()->create([
            'name' => ''
        ]);



    }

    public function render()
    {
        return view('odk-link::livewire.required-data-media');
    }


}
