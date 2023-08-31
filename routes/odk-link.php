<?php

// some random change
// another random change
// Admin panels from the ODK Link Package:
use Stats4sd\OdkLink\Http\Controllers\XlsformTemplateController;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Stats4sd\OdkLink\Http\Controllers\Admin\XlsformCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\OdkProjectCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\SubmissionCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\XlsformSubjectCrudController;
use Stats4sd\OdkLink\Http\Controllers\Admin\XlsformTemplateCrudController;

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin'),
    ),
], function () {

    Route::crud('xlsform-subject', XlsformSubjectCrudController::class);

    Route::crud('xlsform-template', XlsformTemplateCrudController::class);
    Route::get('xlsform-template/{xlsformTemplate}/review', [XlsformTemplateController::class, 'review']);


    // Add a new route for XlSForm Template CRUD list view "Select" button
    Route::get('xlsform-template/{id}/select', [XlsformTemplateCrudController::class, 'select']);
    Route::post('xlsform-template/{id}/submitSelectedFields', [XlsformTemplateCrudController::class, 'submitSelectedFields']);



    Route::crud('xlsform', XlsformCrudController::class);
    Route::crud('odk-project', OdkProjectCrudController::class);
    Route::crud('submission', SubmissionCrudController::class);


    Route::post('submission/{submission}/reprocess', [SubmissionCrudController::class, 'reprocess'])->name('submission.reprocess');

    // XLS Form Crud controller custom routes;
    Route::post('xlsform/{xlsform}/deploy-draft', [XlsformCrudController::class, 'deployDraft'])->name('xlsform.deploy-draft');
    Route::post('xlsform/{xlsform}/deploy-live', [XlsformCrudController::class, 'deployLive'])->name('xlsform.deploy-live');
    Route::post('xlsform/{xlsform}/update-xlsfile', [XlsformCrudController::class, 'updateXlsFileFromTemplate'])->name('xlsform.update-xlsfile');
    Route::post('xlsform/{xlsform}/archive', [XlsformCrudController::class, 'archiveForm'])->name('xlsform.archive');
    Route::post('xlsform/{xlsform}/get-submissions', [XlsformCrudController::class, 'getSubmissions'])->name('xlsform.get-submissions');
});
