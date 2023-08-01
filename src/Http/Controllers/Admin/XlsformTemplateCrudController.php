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
    use DeleteOperation;
    use DropzoneOperation;

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
            ->label('Form Template Title')
            ->type('custom_html')
            ->value(function (XlsformTemplate $entry) {
                $editUrl = backpack_url("xlsform-template/$entry->id/edit");

                return "<a href='{$editUrl}'>$entry->title</a>";
            });

        CRUD::column('odk-link')
            ->label('View on ODK')
            ->type('custom_html')
            ->value(function (XlsformTemplate $entry) {
                return "<a href='{$entry->getOdkLink()}'>View on ODK Central</a>";
            });
        CRUD::column('requiredFixedMedia')
            ->type('closure')
            ->label('No. of Media items')
            ->function(function ($entry) {
                return "{$entry->requiredFixedMedia()->has('attachment')->count()} / {$entry->requiredFixedMedia()->count()}";

            });
        CRUD::column('requiredDataMedia')->type('relationship_count')->label('No. of data files');
        CRUD::column('available')->type('boolean')->label('Form Ready?');

        CRUD::filter('xlsform_subject_id')
            ->type('select2')
            ->label('Filter by Xlsform subject')
            ->options(function () {
                return XlsformSubject::get()->pluck('name', 'id')->toArray();
            })
            ->whenActive(function ($value) {
                $this->crud->addClause('where', 'xlsform_subject_id', $value);
            });

        // add the "Select" button for "Select ODK Variables"
        Crud::button('select')
            ->stack('line')
            ->view('odk-link::buttons.select');

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
        CRUD::removeSaveActions(['save_and_back', 'save_and_show', 'save_and_new', 'save_and_preview']);

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

        CRUD::field('title')
            ->validationRules('required|max:255');

        CRUD::field('xlsfile')
            ->type('upload')
            ->upload(true)
            ->validationRules('required');

        CRUD::field('description')
            ->type('textarea')
            ->validationRules('nullable');

        CRUD::field('xlsformSubject')
            ->type('relationship')
            ->label('Xlsform subject - the data subject of the form')
            ->placeholder('Select the data subject of the form')
            ->validationRules('required')
            ->inline_create([
                'entity' => 'xlsformSubject',
                'modal_route' => route('xlsform-subject-inline-create'),
                'create_route' => route('xlsform-subject-inline-create-save'),
                'add_button_label' => 'Create new data subject',
            ]);

        CRUD::field('create_footer')
            ->type('section-title')
            ->content('When you save, the XLSform file will be uploaded to ODK Central for checking. On the next page, you will see the feedback of this checking.')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation(): void
    {
        $qrString = 'temp';
        $mediaCount = 0;

        if ($entry = $this->crud->getCurrentEntry()) {
            $qrString = $entry->draft_qr_code_string;
            $mediaCount = $entry->requiredMedia()->count();
        }

        // TODO: turn this into a custom "media operation"
        Widget::add()->type('script')
            ->content('assets/js/admin/xlsform_template_media.js');

        CRUD::field('draft_title')
            ->type('section-title')
            ->title('Review Form')
            ->content('Your XLSform file has been uploaded to ODK Central. You can review the draft on ODK Collect using the QR Code below. <div class="my-4 mx-3 d-flex justify-content-center">' .
                QrCode::size(100)->generate($qrString) . '</div>')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');


        CRUD::field('media_title')
            ->type('section-title')
            ->title('Form Attachments + Media')
            ->content('ODK Central has identified that this form requires ' . $mediaCount . ' attachments. These are listed below. For images, videos and audio, please upload a file to be used. For "file" items, you can either upload a data file, or link it to a database table/view. If the data should be different for each team / form owner, it must be linked to the database.')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');

        CRUD::field('requiredMedia')
            ->type('relationship')
            ->subfields([
                [
                    'name' => 'name',
                    'attributes' => [
                        'disabled' => 'disabled'
                    ],
                ],
                [
                    'name' => 'type',
                    'attributes' => [
                        'disabled' => 'disabled'
                    ],
                ],
                [
                    'name' => 'static',
                    'type' => 'checkbox',
                    'label' => 'Is this file identical for all instances of this form?'
                ],
                [
                    'name' => 'file_upload',
                    'type' => 'dropzone',
                    'label' => 'Please upload the file to be used. THis will be included with every instance of this form for all platform users.',
                    'withMedia' => true,
                    'configuration' => [
                        'maxFiles' => 1,
                    ]
                ],
                [
                    'name' => 'dynamic_file_info',
                    'type' => 'section-title',
                    'content' => 'For data files that should be contextualised, please add the following information.',
                    'view_namespace' => 'stats4sd.laravel-backpack-section-title::fields',

                ],
                [
                    'name' => 'mysql_name',
                    'label' => 'MySQL Table/View Name',
                    'hint' => 'Whenever a version of this form is deployed, a new csv file will be generated using the data from this specified database table or view.'
                ],
                [
                    'name' => 'per_owner',
                    'type' => 'checkbox',
                    'label' => 'Does this csv file show owner-specific data?',
                    'hint' => 'Do different users or teams have different data for this csv lookup file?',
                ],
                [
                    'name' => 'owner_foreign_key',
                    'type' => 'text',
                    'label' => 'Which column in the MySQL view or table references the owner of the form?',
                    'hint' => 'e.g. "team_id", or "user_id". The data included in the csv file will be filtered so that only the relevant data will be available in the form.',
                ],
                [
                    'name' => 'external_file',
                    'type' => 'checkbox',
                    'label' => 'Is this file to be used with either a select_one_from_external or select_multiple_from_external question type?',
                    'hint' => 'If you are not sure, leave this unchecked!',
                ],
            ])
            ->min_rows($mediaCount)
            ->max_rows($mediaCount);
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
