<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Prologue\Alerts\Facades\Alert;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stats4sd\OdkLink\Http\Controllers\Admin\Operations\ReviewOperation;
use Stats4sd\OdkLink\Imports\XlsformImport;
use Stats4sd\OdkLink\Models\RequiredMedia;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformSubject;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

/**
 * Class XlsformCrudController
 * @package \Stats4SD\OdkLink\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class XlsformTemplateCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;

    public function getCurrentEntry(): ?XlsformTemplate
    {
        $id = $this->crud->getCurrentEntryId();

        if ($id === false) {
            return null;
        }

        return XlsformTemplate::find($id);
    }

    public function setup(): void
    {
        CRUD::setModel(XlsformTemplate::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/xlsform-template');
        CRUD::setEntityNameStrings('xlsform-template', 'xlsform templates');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation(): void
    {
        $owners = Str::plural(config('odk-link.owners.main_type'));

        CRUD::setResponsiveTable(false);

        Widget::add()
            ->type('card')
            ->content([
                'body' => "The table below lists all the ODK form templates that are available for users of the platform, and the status of each template. Individual {$owners} can deploy a version of these forms that only they have access to.",
            ])
            ->wrapper([
                'class' => 'col-12 col-lg-6 mb-4',
            ])
            ->to('before_content');

        CRUD::column('title')
            ->label('Form Template')
            ->type('custom_html')
            ->value(function (XlsformTemplate $entry) {
                $editUrl = backpack_url("xlsform-template/$entry->id/edit");

                return "<a href='{$editUrl}'>$entry->title</a>";
            });

        CRUD::column('xlsfile')
            ->label('XlsForm File')
            ->type('custom_html')
            ->value(function (XlsformTemplate $entry) {
                $url = Storage::disk(config('odk-link.storage.xlsforms'))->url($entry->xlsfile);

                return "<a href='{$url}'>Download File </a>";
            });

        CRUD::column('requiredFixedMedia')
            ->type('closure')
            ->label('No. of Media items')
            ->function(function (XlsformTemplate $entry) {


                // if the template has not yet been successfully uploaded
                if (!$entry->odk_id) {
                    return '-';
                }

                return "{$entry->attachedFixedMedia()->count()} / {$entry->requiredFixedMedia()->count()}";

            });

        CRUD::column('requiredDataMedia')
            ->type('closure')
            ->label('No. of data files')
            ->function(function (XlsformTemplate $entry) {

                // if the template has not yet been successfully uploaded
                if (!$entry->odk_id) {
                    return '-';
                }

                return "{$entry->attachedDataMedia()->count()} / {$entry->requiredDataMedia()->count()}";
            });

        CRUD::column('primary_dataset')
            ->type('closure')
            ->label('Main dataset')
            ->function(function(XlsformTemplate $entry) {
                return $entry->primary_dataset ? $entry->primary_dataset->name : '-';
            });


        CRUD::column('available')->type('custom_html')->label('Form Status')
            ->value(function (XlsformTemplate $entry) {
                if ($entry->odk_error) {
                    return "<span class='text-danger'>XLSFORM ERROR</span>";
                }

                return $entry->available ? "Available" : "Pending";
            });

        CRUD::button('review')
            ->stack('line')
            ->view('odk-link::buttons.xlsformtemplate.review')
            ->before('delete');

        CRUD::button('publish')
            ->stack('line')
            ->view('odk-link::buttons.xlsformtemplate.publish')
            ->before('delete');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation(): void
    {
        CRUD::removeAllSaveActions();
        CRUD::addSaveAction([
            'name' => 'save',
            'redirect' => function ($crud, $request, $itemId) {
                return backpack_url('xlsform-template/' . $itemId . '/review');
            },
            'button_text' => 'Save & Review',
        ]);

        CRUD::field('create_header')
            ->type('section-title')
            ->title('Step 1: Upload XLSForm File')
            ->content('Below, please upload the XLSform file. The file will be sent to ODK Central for checking. After saving:<ul>
                <li>If there are ODK form errors, you will see them here. Please check the XLSform file and re-upload.</li>
                <li>If there are no errors, you will be taken back to the review page, where you can add any required media files, datasets and preview the form.</li>
                </ul>
            ')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');

        if ($error = CRUD::getCurrentEntry()?->odk_error ?? false) {
            CRUD::field('error_display')
                ->type('section-title')
                ->view_namespace('stats4sd.laravel-backpack-section-title::fields')
                ->variant('danger')
                ->content("<h4>XLSForm Error reported by ODK Central</h4>{$error}");
        }

        CRUD::field('title')
            ->validationRules('required|max:255');

        CRUD::field('xlsfile')
            ->type('upload')
            ->upload(true)
            ->validationRules('sometimes|required');

        CRUD::field('description')
            ->type('textarea')
            ->validationRules('nullable');

        // add a field to select the main dataset

    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    // Select button, divert to a fully customized blade view file
    public function select()
    {
        $entry = CRUD::getCurrentEntry();

        // read excel files into an array of excel sheets
        $sheets = Excel::toArray(new XlsformImport, $entry->xlsfile);

        // get the "survey" excel data sheet, which is the survey template
        $surveySheet = $sheets['survey'];

        // convert selected fields from CSV to array
        $selectedFields = str_getcsv($entry->selected_fields);

        return view('odk-link::xlsformtemplate.select-odk-variables',
            [
                'entry' => $entry,
                'surveySheet' => $surveySheet,
                'selectedFields' => $selectedFields,
            ]
        );
    }

    // handle the form submission for user selected ODK variables
    public function submitSelectedFields(Request $request)
    {
        // get all form data from POST request
        $data = $request->all();

        // remove form data "_token", remaining items are user selected fields
        unset($data['_token']);

        // divide array into keys array and values array
        list($keys, $values) = Arr::divide($data);

        // convert values array to a CSV string
        $selectedFields = implode(",", $values);

        // store CSV in database column
        $entry = CRUD::getCurrentEntry();
        $entry->selected_fields = $selectedFields;
        $entry->save();

        // divert to CRUD panel list view
        return redirect('/admin/xlsform-template');
    }

    // override the destroy function to delete the xlsform file from storage
    public function destroy($id)
    {

        $entry = XlsformTemplate::find($id);

        // if the template is available, prevent deletion
        if ($entry->available) {

            Alert::add('danger', 'This form is available to users and cannot be deleted from the platform. Please archive it instead to prevent further submissions.')->flash();

            return redirect()->back();
        }

        // delete the xlsform file from storage
        Storage::disk(config('odk-link.storage.xlsforms'))->delete($entry->xlsfile);

        // delete the entry from the database
        return parent::destroy($id);
    }

    public function archive(XlsformTemplate $xlsformTemplate)
    {
        // make the form unavailable to teams
        $xlsformTemplate->update([
            'available' => false,
        ]);

        Alert::add('success', 'Form archived successfully')->flash();

        return redirect()->back();

    }

}
