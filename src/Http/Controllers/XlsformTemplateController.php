<?php

namespace Stats4sd\OdkLink\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Stats4sd\OdkLink\Models\XlsformTemplate;

class XlsformTemplateController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    // REVIEW FORM

    public function review(XlsformTemplate $xlsformTemplate)
    {
        $xlsformTemplate->load([
            'requiredFixedMedia',
            'requiredDataMedia',
        ]);

        return view('odk-link::xlsformtemplate.review', [
            'xlsformTemplate' => $xlsformTemplate,
        ]);
    }


}
