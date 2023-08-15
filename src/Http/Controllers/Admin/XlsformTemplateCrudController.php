<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stats4sd\OdkLink\Http\Controllers\Admin\Operations\ReviewOperation;
use Stats4sd\OdkLink\Imports\XlsformImport;
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
    use DropzoneOperation;
    use ReviewOperation;
    use DeleteOperation;

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


        CRUD::column('available')->type('custom_html')->label('Form Status')
            ->value(function (XlsformTemplate $entry) {
                if ($entry->odk_error) {
                    return "<span class='text-danger'>XLSFORM ERROR</span>";
                }

                return $entry->available ? "Available" : "Pending";
            });

//        CRUD::filter('xlsform_subject_id')
//            ->type('select2')
//            ->label('Filter by Xlsform subject')
//            ->options(function () {
//                return XlsformSubject::get()->pluck('name', 'id')->toArray();
//            })
//            ->whenActive(function ($value) {
//                $this->crud->addClause('where', 'xlsform_subject_id', $value);
//            });

//        // add the "Select" button for "Select ODK Variables"
//        Crud::button('select')
//            ->stack('line')
//            ->view('odk-link::buttons.select');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation(): void
    {
        // only "save and edit" should be available
        CRUD::removeSaveActions(['save_and_edit', 'save_and_show', 'save_and_new', 'save_and_preview']);

        CRUD::field('create_header')
            ->type('section-title')
            ->title('Step 1: Upload XLSForm File')
            ->content('In this first step, please upload the XLSform file. The file will be sent to ODK Central for checking. After saving, you will be able to:<ul>
                <li>Review any ODK errors that were found in the form (TODO)</li>
                <li>Add required media files, or link the form to database tables if you want csv files that automatically update and/or that contain owner-specific data</li>
                <li>Automatically publish the form to all platform users, or only to specific users. (TODO)</li>
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

        CRUD::field('create_footer')
            ->type('section-title')
            ->content('When you save, the XLSform file will be uploaded to ODK Central for checking. On the next page, you will see the feedback of this checking.')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');
    }

    public function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    protected function setupReviewOperation(): void
    {
        $entry = $this->crud->getCurrentEntry();

        $qrString = $entry?->draft_qr_code_string ?? null;

        if ($qrString) {

            CRUD::field('draft_title')
                ->type('section-title')
                ->title("Review Form - {$entry?->title}")
                ->content("Your XLSform file has been uploaded to ODK Central. You can review the draft using ODK Collect or Enketo. We recommend previewing the form with the same tool that will be used for data collection, because Enketo and ODK Collect render the same form in quite different ways. <br/><br/>

                    Note that this is not intended to be used for any real data collection. No submissions are kept from these forms. You may also find the form does not work properly if there are missing media files or datasets.<br/><br/>
                    <h3>Preview in ODK Collect</h3>
                    In ODK Collect, go to 'add new project' and then scan the QR code below. THis will create a new project with <b>only</b> this form. Once you have finished testing the form, you can delete that entire project from ODK Collect to keep your project list tidy.
                    <div class='my-4 mx-3 d-flex justify-content-start'>" .
                        QrCode::size(200)->generate($qrString) .
                    "</div>
                    <h3 class='mt-4'>Preview in Enketo</h3>
                    <a href='" .
                        config('odk-link.odk.url') .
                        "/-/{$entry->enketo_draft_url}' target='_blank'>Preview the form in Enketo webforms here</a>.

                    ")
                ->view_namespace('stats4sd.laravel-backpack-section-title::fields');

        }


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

}
