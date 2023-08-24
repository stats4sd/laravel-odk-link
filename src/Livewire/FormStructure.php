<?php

namespace Stats4sd\OdkLink\Livewire;

use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stats4sd\OdkLink\Models\Dataset;
use Stats4sd\OdkLink\Models\DatasetVariable as DatasetVariableModel;
use Stats4sd\OdkLink\Models\RequiredMedia;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Symfony\Contracts\Service\Attribute\Required;

class FormStructure extends Component
{

    public XlsformTemplate $xlsformTemplate;
    public ?Dataset $rootDataset;
    public Collection $datasets;
    public string $rootDatasetId;

    // for creating and linking new datasets;
    public string $newDatasetName;
    public string $structure;
    public bool $is_repeat;

    public function mount(XlsformTemplate $xlsformTemplate)
    {
        $this->xlsformTemplate = $xlsformTemplate;
        $this->rootDatasetId = $xlsformTemplate->root_dataset_id ?? '';
        $this->datasets = Dataset::all();

        // for creating and linking new datasets;
        $this->newDatasetName = '';
        $this->structure = '';
        $this->is_repeat = false;
    }

    public function pickDataset()
    {
        $this->rootDataset = Dataset::find($this->rootDatasetId);
    }

    public function createRootDataset()
    {
        $this->structure = 'root';
        $this->is_repeat = false;
    }

    public function storeDataset()
    {
        $dataset = Dataset::create([
            'name' => $this->newDatasetName,
        ]);

        $this->xlsformTemplate->datasets()
            ->sync($dataset->id, [
                'is_root' => $this->structure === 'root',
                'is_repeat' => $this->is_repeat,
                'stricture_item' => $this->structure,
            ]);

        if ($this->structure === 'root') {
            $this->rootDatasetId = $dataset->id;
            $this->pickDataset();
        }
    }

    public function render()
    {
        return view('odk-link::livewire.form-structure');

    }

}
