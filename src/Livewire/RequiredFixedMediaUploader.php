<?php

namespace Stats4sd\OdkLink\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stats4sd\OdkLink\Models\RequiredMedia;

class RequiredFixedMediaUploader extends Component
{
    use WithFileUploads;

    public RequiredMedia $requiredMedia;
    public $attachment;

    public function getRules()
    {
        return [
            'file',
            $this->requiredMedia->type
        ];
    }

    #[Computed]
    public function fileUrl()
    {
        return $this->requiredMedia->getFirstMedia()->getUrl();
    }

    public function save()
    {
        $this->validate(['attachment' => $this->getRules()]);

        // remove existing files from requiredMedia item
        $this->remove();

        // add uploaded file to the media library;
        $this->requiredMedia->addMedia($this->attachment)->toMediaLibrary();

        // associate the new media library entry as the "attachment".
        $this->requiredMedia->attachment()->associate($this->requiredMedia->getFirstMedia());

        // as we have not made any changes directly to the requiredMedia (only to its relations), we must refresh the model to force Livewire to update the related parts of the DOM.
        $this->requiredMedia->refresh();
        $this->reset('attachment');

    }

    public function remove()
    {
        $this->requiredMedia->attachment()->disassociate();

        $this->requiredMedia->getMedia()
        ->each(fn($media) => $this->requiredMedia->deleteMedia($media));

        $this->requiredMedia->refresh();
    }

    public function removeAttachment()
    {
        $this->reset('attachment');
    }

    public function removeAndReset()
    {
        $this->remove();
        $this->removeAttachment();
    }

    public function render()
    {
        return view('odk-link::livewire.required-fixed-media-uploader');
    }


}
