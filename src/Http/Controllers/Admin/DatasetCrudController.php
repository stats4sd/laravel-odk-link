<?php

namespace Stats4sd\OdkLink\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Stats4sd\OdkLink\Models\Dataset;

/**
 * Class DatasetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DatasetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    {
        CRUD::setModel(Dataset::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dataset');
        CRUD::setEntityNameStrings('dataset', 'datasets');
    }

    protected function setupListOperation(): void
    {
        \Widget::add()
            ->to('before_content')
            ->type('alert')
            ->class('alert alert-info text-dark')
            ->content('<p>Datasets are the main way of organising data that users of the platform collect and manage. Each dataset has a name, a description and a primary key variable.</p>
                        <p>There are 2 main uses of datasets:
                            <ul>
                                <li>to organise data collected through ODK forms</li>
                                <li>to share data back to ODK forms as csv lookup files.</li>
                            </ul>
                        </p>
                        <p>Datasets are kept separate per team, but users with administrative access can review data from all teams together. For some platforms, this might depend on teams agreeing to share their data with the platform managers. </p>
                        <p>For example, if you are collecting data on farmers, you might have a dataset called "Farmers", and an XLSform template called "Farmer Registration". Then, whenever a submission is received for the Farmer Registration form, the farmer data will be added to the dataset for that user/team.</p>
                        <p>When adding a dataset to this list, you do not need to specify every variable - that is done automatically when you add an XLSForm template. You must specify a primary key - this will be used to recognise if a form  submission should create a new record or update an existing record.</p>


            ');

        CRUD::column('name');
        CRUD::column('primary_key');
        CRUD::column('entity_model');

    }

    public function setupShowOperation(): void
    {
        CRUD::column('name');
        CRUD::column('primary_key');
        CRUD::column('entity_model');
        CRUD::column('description');
        CRUD::column('xlsformTemplates')->type('relationship')->label('Xlsform Templates that update this dataset')->attribute('name');
        CRUD::column('requiredMedia')->type('closure')->label('Xlsform Templates that use this dataset for lookup data')->attribute('name')
        ->function(function($entry) {
            return $entry->requiredMedia->map(function($media) {
                return $media->xlsformTemplate->title;
            })->implode(', ');
        });
    }

    protected function setupCreateOperation(): void
    {
        CRUD::field('name')->validationRules('required')->label('Enter the name of the dataset');
        CRUD::field('primary_key')->validationRules('required')->label('Enter an identifiable name for the primary key variable. It should be recognisable as a unique key for this dataset. For example "farmer_id" is better than "id". ');
        CRUD::field('description')
            ->type('textarea')
            ->validationRules('nullable')->label('Enter a brief description of the dataset')
            ->hint('For example, what is the dataset describing? E.g. farmers, fields, plots, etc.');

        CRUD::field('variables')
            ->label('If this dataset is used for lookup data in a form, you must specify the variables that can be used in the ODK form. Any variables added here will be included in csv lookup files generated for this dataset.')
            ->hint('Note - you do not need to specify every variable in the dataset. Only add variables that you want to be available for lookup in ODK forms.')
            ->subfields([
                [
                    'name' => 'name',
                    'label' => 'Variable name',
                    'hint' => 'The name of the variable as it appears in the ODK form. For example, "farmer_id".'
                ],
            ])
        ->new_item_label('Add a variable');

    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
