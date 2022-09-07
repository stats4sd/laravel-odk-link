<?php

// Admin panels from the ODK Link Package:
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
//    Route::crud('xlsform-version', XlsformVersionCrudController::class);
    Route::crud('submission', SubmissionCrudController::class);

    // XLS Form Crud controller custom routes;
    Route::post('xlsform/{xlsform}/deploy-draft', [XlsformCrudController::class, 'deployDraft'])->name('xlsform.deploy-draft');
    Route::post('xlsform/{xlsform}/deploy-live', [XlsformCrudController::class, 'deployLive'])->name('xlsform.deploy-live');
    Route::post('xlsform/{xlsform}/update-xlsfile', [XlsformCrudController::class, 'updateXlsFileFromTemplate'])->name('xlsform.update-xlsfile');
    Route::post('xlsform/{xlsform}/archive', [XlsformCrudController::class, 'archiveForm'])->name('xlsform.archive');


//    // XLS Form <--> KoBoToolbox handling
//    Route::post('teamxlsform/{form}/deploytokobo', [TeamXlsformCrudController::class, 'deployToKobo']);
//    Route::post('teamxlsform/{form}/syncdata', [TeamXlsformCrudController::class, 'syncData']);
//    Route::post('teamxlsform/{form}/archive', [TeamXlsformCrudController::class, 'archiveOnKobo']);
//    Route::post('teamxlsform/{form}/csvgenerate', [TeamXlsformCrudController::class, 'regenerateCsvFileAttachments']);
//    Route::get('teamxlsform/{form}/downloadsubmissions', [TeamXlsformCrudController::class, 'downloadSubmissions'])->name('team_xlsforms.submissions');
//
//    Route::post('submission/{submission}/reprocess', [SubmissionCrudController::class, 'reprocessSubmission']);

Route::get('testing/{xlsform}/upload-file', function(Xlsform $xlsform, OdkLinkService $odkLinkService){
    $odkLinkService->uploadMediaFileAttachments($xlsform);

});

});


