<?php

namespace Stats4sd\OdkLink\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stats4sd\OdkLink\Models\RequiredMedia;

class RequiredDataMediaUploader extends Component
{
    use WithFileUploads;

    public RequiredMedia $requiredMedia;

    public function render()
    {
        return view('odk-link::livewire.required-data-media-uploader');
    }


}
