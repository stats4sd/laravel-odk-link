<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\Widget;
use Stats4sd\OdkLink\Models\XlsformSubject;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

/**
 * Class XlsformCrudController
 * @package \Stats4SD\OdkLink\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class XlsformSubjectCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;

    public function setup(): void
    {
        CRUD::setModel(XlsformSubject::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/xlsform-subject');
        CRUD::setEntityNameStrings('xlsform subject', 'xlsform subjects');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation(): void
    {
        Widget::add()
                ->to('before_content')
                ->type('card')
                ->wrapper(['class' => 'col-md-12 col-lg-10'])
                ->content(
                    [
                        'body' => 'The table below shows the different data subject options for an xlsform template.'
                    ]
                );
                
        CRUD::column('name');
        
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation(): void
    {
        CRUD::field('name')
            ->validationRules('required');

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

    }

}
