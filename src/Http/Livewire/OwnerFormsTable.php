<?php

namespace Stats4sd\OdkLink\Http\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Stats4sd\OdkLink\Models\OdkProject;

class OwnerFormsTable extends Component
{
    public OdkProject $odkProject;
    public Collection $xlsforms;


    public function render()
    {
        return view('odk-link::livewire.owner-forms-table');
    }
}
