<?php

namespace Stats4sd\OdkLink\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Stats4sd\OdkLink\Models\RequiredMedia;
use Stats4sd\OdkLink\Models\XlsformTemplate;

class RequiredMediaController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;


    // handle uploaded files
    public function updateMediaFile(RequiredMedia $requiredMedia): array
    {
        // un-link any existing dataset?
        $requiredMedia->dataset()->disassociate();

        $requiredMedia->getMedia()
            ->each(fn($media) => $requiredMedia->deleteMedia($media));


        $requiredMedia->addMediaFromRequest('uploaded_file')->toMediaLibrary();

        // if we're uploading a file, the required media is always static
        $requiredMedia->is_static = true;
        $requiredMedia->save();

        $requiredMedia->refresh();

        return ['required_media' => $requiredMedia];
    }

    // link a required media to a dataset
    public function linkToDataset(RequiredMedia $requiredMedia): array
    {
        request()->validate(['dataset_id' => 'required']);

        $requiredMedia->dataset()->associate(request()->dataset_id);
        $requiredMedia->is_static = false;
        $requiredMedia->save();

        $requiredMedia->refresh();

        return ['required_media' => $requiredMedia];
    }

}
