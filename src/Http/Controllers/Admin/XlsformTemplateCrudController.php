<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use config;
use Illuminate\Support\Facades\Storage;
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
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(XlsformTemplate::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/xlsform-template');
        CRUD::setEntityNameStrings('xlsform template', 'xlsform templates');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation(): void
    {
        CRUD::column('title');
        CRUD::column('xlsfile')->type('upload')->wrapper([
            'href' => function ($crud, $column, $entry) {
                if ($entry->xlsfile) {
                    return Storage::disk(config('odk-link.storage.xlsforms'))->url($entry->xlsfile);
                }

                return '#';
            },
        ]);
        CRUD::column('media')->type('upload_multiple')->disk(config('odk-link.storage.xlsforms'));
        CRUD::column('csv_lookups')->type('table')->columns([
            'mysql_name' => 'MySQL Table/View',
            'csv_name' => 'CSV File Name',
        ]);
        CRUD::column('available')->type('boolean')->label('Is the form available for live use?');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation(): void
    {
        CRUD::field('title')
            ->validationRules('required|max:255');

        CRUD::field('xlsfile')
            ->type('upload')
            ->upload(true)
            ->validationRules('required');

        CRUD::field('description')
            ->type('textarea')
            ->validationRules('nullable');

        CRUD::field('media')
            ->type('upload_multiple')
            ->label('Add any static files that should be pushed to KoboToolBox as media attachments for this form')
            ->upload(true)
            ->validationRules('nullable');


        CRUD::field('csv_lookups')->type('repeatable')->subfields([
            [
                'name' => 'mysql_name',
                'label' => 'MySQL Table Name',
                'validationRules' => 'required',
                'validationMessages' => [
                    'required' => 'Please add the name of the MySQL view that holds the data for the CSV file.',
                ]
            ],
            [
                'name' => 'csv_name',
                'label' => 'CSV File Name',
                'validationRules' => 'required',
                'validationMessages' => [
                    'required' => 'Please add the name of the CSV file that should be attached to the ODK form.',
                ]
            ],
            [
                'name' => 'per_owner',
                'type' => 'checkbox',
                'label' => 'Does this csv file show owner-specific data?',
                'hint' => '(Do different users or teams have different data for this csv lookup file?)',
            ],
            [
                'name' => 'owner_foreign_key',
                'type' => 'text',
                'label' => 'Which column in the MySQL view or table references the owner of the form?',
                'hint' => 'e.g. "team_id", or "user_id" ',
            ],
            [
                'name' => 'external_file',
                'type' => 'checkbox',
                'label' => 'Is this file to be used with either a select_one_from_external or select_multiple_from_external question type?',
                'hint' => 'If you are not sure, leave this unchecked!',
            ],
        ])->label('
        <h4>Add Lookups from the Database</h4><br/>
        <div class="bd-callout bd-callout-info font-weight-normal">
            You should add the name of the MySQL Table or View, and the required name of the resulting CSV file. Every time you deploy this form, the platform will create a new version of the csv file using the data from the MySQL table or view you specify. This file will be uploaded to KoboToolBox as a form media attachment.
            <br/><br/>
            For example, if the form requires a csv lookup file called "households.csv", and the data is available in a view called "households_csv", then you should an entry like this:
            <ul>
                <li>MySQL Table Name = households_csv</li>
                <li>CSV File Name = households</li>
            </ul>
            CSV files can optionally be filtered to only show team-specific records. Use this for data that each team can customise themselves, or for data that should be filtered to a team\'s local context. For this to work, the MySQL table or view <b>must</b> have a "team_id" field to filter by.
        </div>
        ')->entity_singular('CSV Lookup reference');

        CRUD::field('available')
            ->label('If this form should be available to all users, tick this box')
            ->type('checkbox');
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();

        CRUD::removeField('xlsfile');

        CRUD::field('xlsfile')
            ->after('title')
            ->type('upload')
            ->upload(true)
            ->validationRules('nullable');
    }

    public function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);

        $this->setupListOperation();
    }
}
