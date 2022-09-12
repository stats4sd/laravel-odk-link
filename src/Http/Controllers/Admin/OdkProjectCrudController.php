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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class XlsformCrudController
 * @package \Stats4SD\OdkLink\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class OdkProjectCrudController extends CrudController
{
    use ListOperation;
    use ShowOperation;

    /**
     * @throws Exception
     */
    public function setup()
    {
        CRUD::setModel(OdkProject::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/odk-project');
        CRUD::setEntityNameStrings('XLS Form Owner', 'XLS Form Owners');

        CRUD::setShowView('odk-link::odk-projects.show');
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

        CRUD::column('owner')->label('Owner')->type('relationship')->attribute('name')->wrapper([
            'href' => function ($crud, $column, $entry) {
                return $entry->getOdkLink();
            }
        ]);
        CRUD::column('owner_type')->type('closure')->function(function($entry) {
            return collect(explode("\\", $entry->owner_type))->last();
        });

        CRUD::column('appUsers')
            ->label('Enumerator Accounts Created')
            ->suffix(' Account(s)')
            ->type('relationship_count');

        CRUD::column('xlsforms')
        ->label('Number of XLS Forms')
        ->suffix(' Form(s)')
        ->type('relationship_count');
    }


}
