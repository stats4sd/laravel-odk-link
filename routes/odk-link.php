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

    // Add a new route for XlSForm Template CRUD list view "Select" button
    Route::get('xlsform-template/{id}/select', [XlsformTemplateCrudController::class, 'select']);

    // Add a new route to handle the form submission for user selected ODK variables
    Route::post('xlsform-template/{id}/submitSelectedFields', [XlsformTemplateCrudController::class, 'submitSelectedFields']);

    Route::crud('xlsform', XlsformCrudController::class);
    Route::crud('odk-project', OdkProjectCrudController::class);

//    Route::crud('xlsform-version', XlsformVersionCrudController::class);
    Route::crud('submission', SubmissionCrudController::class);

    // XLS Form Crud controller custom routes;
    Route::post('xlsform/{xlsform}/deploy-draft', [XlsformCrudController::class, 'deployDraft'])->name('xlsform.deploy-draft');
    Route::post('xlsform/{xlsform}/deploy-live', [XlsformCrudController::class, 'deployLive'])->name('xlsform.deploy-live');
    Route::post('xlsform/{xlsform}/update-xlsfile', [XlsformCrudController::class, 'updateXlsFileFromTemplate'])->name('xlsform.update-xlsfile');
    Route::post('xlsform/{xlsform}/archive', [XlsformCrudController::class, 'archiveForm'])->name('xlsform.archive');

});
