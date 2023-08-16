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

    }

}
