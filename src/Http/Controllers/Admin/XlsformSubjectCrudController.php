<?php


namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\InlineCreateOperation;
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
    use InlineCreateOperation;

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
        CRUD::field('description')
            ->type('section-title')
            ->title('Data Subjects')
            ->content('Within this system, each form must have a "primary data subject". If you\'re not sure what that means, consider the question "what are you collecting data about"? It might be that your form collects data at multiple levels. If this is the case, then what is the main level? E.g., if you are collecting 1 submission per household, your data subject is probably "household", even if you have a section that asks questions to each household member in turn. If you are collecting 1 submission per household member, your data subject is probably "household member"')
            ->view_namespace('stats4sd.laravel-backpack-section-title::fields');

        CRUD::field('name')
            ->label('What is the name or label for the data subject?')
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
