<?php

namespace Stats4sd\OdkLink\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Services\OdkLinkService;

class XlsformTemplateController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    // REVIEW FORM
    public function review(XlsformTemplate $xlsformTemplate): \Illuminate\View\View
    {
        $xlsformTemplate->load([
            'requiredFixedMedia',
            'requiredDataMedia',
        ]);

        return view('odk-link::xlsformtemplate.review', [
            'xlsformTemplate' => $xlsformTemplate,
        ]);
    }

    // manually redeploy draft form to ODK Central
    public function deployDraft(XlsformTemplate $xlsformTemplate): View
    {
        $odkLinkService = app()->make(OdkLinkService::class);
        $xlsformTemplate->deployDraft($odkLinkService);

        $xlsformTemplate->draft_needs_updating = false;
        $xlsformTemplate->save();


        return view('odk-link::xlsformtemplate.review', [
            'xlsformTemplate' => $xlsformTemplate,
        ]);
    }

    // make the form available to platform users
    public function publish(XlsformTemplate $xlsformTemplate)
    {
        $xlsformTemplate->update([
            'available' => true,
        ]);

        Alert::add('success', 'Form published successfully')->flash();

        return redirect()->back();
    }

}
