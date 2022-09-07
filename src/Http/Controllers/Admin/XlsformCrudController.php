<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class XlsformCrudController
 * @package \Stats4SD\OdkLink\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class XlsformCrudController extends CrudController
{
    use ListOperation;

    //use UpdateOperation;

    /**
     * @throws Exception
     */
    public function setup()
    {
        CRUD::setModel(Xlsform::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/xlsform');
        CRUD::setEntityNameStrings('XLS Form', 'XLS Forms');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        CRUD::setResponsiveTable(false);
        CRUD::enableDetailsRow();
        CRUD::setDetailsRowView('odk-link::xlsforms.details_row');

        // TODO: update this to enable users to specify their own identifiable attributes for owners
        CRUD::column('owner')->label('Owner')->type('relationship')->attribute('name');
        CRUD::column('title');
        CRUD::column('xlsformTemplate')->type('relationship')->attribute('title');
        CRUD::column('xlsfile')->type('upload')->disk(config('odk-link.storage.xlsforms'))->wrapper([
            'href' => function ($crud, $column, $entry) {
                if ($entry->xlsfile) {
                    return Storage::disk(config('odk-link.xlsforms.storage_disk'))->url($entry->xlsfile);
                }

                return '#';
            },
        ]);


        CRUD::column('odk_id')->label('ODK Form ID')->wrapper([
            'href' => function ($crud, $column, $entry) {
                return $entry->getOdkLink();
            },
        ]);

        Widget::add()
            ->type('card')
            ->content([
                'header' => 'XLS Forms',
                'body' => 'This page shows the full set of ODK Forms available to you as a user.'
            ]);

    }

    /**
     * @throws RequestException
     */
    public function deployDraft(Xlsform $xlsform, OdkLinkService $odkLinkService): Response
    {
        $odkXlsFormDetails = $odkLinkService->createDraftForm($xlsform);


        $xlsform->update([
            'odk_id' => $odkXlsFormDetails['xmlFormId'],
            'odk_draft_token' => $odkXlsFormDetails['draftToken'],
            'odk_version_id' => $odkXlsFormDetails['version'],
            'has_draft' => true,
            'enketo_draft_url' => $odkXlsFormDetails['enketoId'],
        ]);


        return response("Successfully created draft form on ODK Central");
    }

    public function deployLive(Xlsform $xlsform, OdkLinkService $odkLinkService): Response
    {
        if (!$xlsform->has_draft) {
            return response([
                "type" => "warning",
                "message" => "You must have a draft deployment before publishing to live"
            ]);
        }
        $odkXlsFormDetails = $odkLinkService->publishForm($xlsform);

        $xlsform->update([
            'has_draft' => false,
            'is_active' => true,
            'odk_version_id' => $odkXlsFormDetails->version,
        ]);

        return response([
            "type" => 'success',
            "message" => "Successfully published form on ODK Central"
        ]);

    }

    public function archiveForm(Xlsform $xlsform, OdkLinkService $odkLinkService): Response
    {
        if (!xlsform->is_active) {
            return response([
                "type" => "warning",
                "message" => "The form is currently not active"
            ]);
        }

        $odkXlsFormDetails = $odkLinkService->archiveForm($xlsform);

        $xlsform->update([
            'is_active' => false,
        ]);
    }

    public function updateXlsFileFromTemplate(Xlsform $xlsform)
    {
        $xlsform->updateXlsfileFromTemplate();
        return $xlsform;
    }


}
