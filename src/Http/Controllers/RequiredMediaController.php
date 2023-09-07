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



        $validated = request()->validate([
            'uploaded_file' => 'file|required',
            'is_static' => 'boolean|required',
        ]);


        // un-link any existing dataset?
        $requiredMedia->dataset()->disassociate();

        $requiredMedia->getMedia()
            ->each(fn($media) => $requiredMedia->deleteMedia($media));


        $requiredMedia->addMediaFromRequest('uploaded_file')->toMediaLibrary();

        // check if the item is static from request
        $requiredMedia->is_static = $validated['is_static'];
        $requiredMedia->save();

        $requiredMedia->refresh();

        return ['required_media' => $requiredMedia];
    }

    // link a required media to a dataset
    public function linkToDataset(RequiredMedia $requiredMedia): array
    {
        request()->validate(['dataset_id' => 'required', 'is_static' => 'required|boolean']);

        $requiredMedia->dataset()->associate(request()->dataset_id);
        $requiredMedia->is_static = false;
        $requiredMedia->save();

        $requiredMedia->refresh();

        return ['required_media' => $requiredMedia];
    }

}
