<?php

namespace Stats4sd\OdkLink\Livewire;

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
use Symfony\Contracts\Service\Attribute\Required;

class DatasetVariable extends Component
{

    public string $name;
    public DatasetVariableModel $variable;

    public function mount(DatasetVariableModel $variable)
    {
        $this->variable = $variable;
        $this->name = $variable->name;
    }

    public function updated($name, $value)
    {
        $this->variable->update([$name => $value]);
    }

    public function render()
    {
        return view('odk-link::livewire.dataset-variable');

    }

}
