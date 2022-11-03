<?php

// Admin panels from the ODK Link Package:
use Stats4sd\OdkLink\Http\Controllers\Admin\OdkProjectCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\SubmissionCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\XlsformCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\XlsformTemplateCrudController;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Services\OdkLinkService;

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin'),
    ),
], function () {

    Route::crud('xlsform-template', XlsformTemplateCrudController::class);
    Route::crud('xlsform', XlsformCrudController::class);
    Route::crud('odk-project', OdkProjectCrudController::class);
    Route::crud('submission', SubmissionCrudController::class);


    // ********** XLSFORM ROUTES ********************** //
    Route::post('xlsform/{xlsform}/deploy-draft', [XlsformCrudController::class, 'deployDraft'])->name('xlsform.deploy-draft');
    Route::post('xlsform/{xlsform}/deploy-live', [XlsformCrudController::class, 'deployLive'])->name('xlsform.deploy-live');
    Route::post('xlsform/{xlsform}/update-xlsfile', [XlsformCrudController::class, 'updateXlsFileFromTemplate'])->name('xlsform.update-xlsfile');
    Route::post('xlsform/{xlsform}/archive', [XlsformCrudController::class, 'archiveForm'])->name('xlsform.archive');

    // ******** ODK FORM SUBMISSION ROUTES *************** //
    Route::post('submission/{submission}/reprocess', [SubmissionCrudController::class, 'reprocessSubmission']);



Route::get('testing/{project}/gen-user', function(\Stats4sd\OdkLink\Models\OdkProject $project, OdkLinkService $odkLinkService){
    $odkLinkService->createProjectUser($project);

});

Route::get('testing/app-users', function(OdkLinkService $odkLinkService) {
    return $odkLinkService->test();
});

});


