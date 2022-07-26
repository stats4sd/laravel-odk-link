<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use JsonException;
use Stats4sd\OdkLink\Jobs\ProcessSubmission;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\Xlsform;

/**
 * Class SubmissionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class SubmissionCrudController extends CrudController
{
    use ListOperation;
    use ShowOperation;


    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     * @throws Exception
     */
    public function setup(): void
    {
        CRUD::setModel(Submission::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/submission');
        CRUD::setEntityNameStrings('submission', 'submissions');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation(): void
    {

        CRUD::column('xlsform_title')->label('XLS Form')->limit(1000);
        CRUD::column('xlsformVersion')->label('Form Version')->type('relationship')->attribute('version');
        CRUD::column('id')->label('Submission ID');
        CRUD::column('submitted_at')->type('datetime')->format('YYYY-MM-DD HH:mm:ss');
        CRUD::column('processed')->label('Processed?')->type('boolean');
        CRUD::column('errors')->label('Validation errors')->type('submission_errors')->view_namespace('kobo-link::crud.columns');
        CRUD::column('entries')->label('Db Entries created')->type('submission_entries')->view_namespace('kobo-link::crud.columns');

        CRUD::filter('xlsform')
            ->type('select2')
            ->label('Filter by Xls Form')
            ->values(function () {
                return Xlsform::get()->pluck('title', 'id')->toArray();
            })
            ->whenActive(function ($value) {
                $this->crud->query->whereHas('xlsformVersion', function ($query) use ($value) {
                    $query->where('xlsform_id', $value);
                });
            });

        CRUD::filter('errors')
            ->type('simple')
            ->label('Show submissions with errors')
            ->whenActive(function () {
                CRUD::addClause('where', 'errors', '!=', null);
            });

        Crud::button('reprocess')
            ->stack('line')
            ->view('odk-link::buttons.submissions.reprocess');
    }


    /*
     * Shows the full contents of the submissions. Repeat groups are shown as json objects, which is not ideal but was much easier to create.
     */
    public function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::column('content')->type('custom_html')->value(/**
         * @throws JsonException
         */ function ($entry) {
            if (!is_array($entry->content)) {
                $content = json_decode($entry->content, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $content = $entry->content;
            }

            return $this->createContentTable($content);
        });
    }

    public function createContentTable($array)
    {
        $output = '
            <table class="table table-striped">
            <tr>
            <th>Variable Name</th>
            <th>Value</th>
            </tr>
            ';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($key === "__system" || $key === "meta") {
                    $value = json_encode($value);
                } else {
                    $value = $this->createContentTable($value);

                }
            }

            $output .= '
                <tr>
                    <td>' . $key . '</td>
                    <td>' . $value . '</td>
                </tr>
                ';
        }

        $output .= '</table>';

        return $output;
    }
}
