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
    public function update(RequiredMedia $requiredMedia)
    {
        // delete any existing media
        $requiredMedia->attachment()->disassociate();

        $requiredMedia->getMedia()
            ->each(fn($media) => $requiredMedia->deleteMedia($media));


        $requiredMedia->addMediaFromRequest('uploaded_file')->toMediaLibrary();
        $requiredMedia->attachment()->associate($requiredMedia->getFirstMedia());

        $requiredMedia->refresh();

        return ['required_media' => $requiredMedia];
    }

}
