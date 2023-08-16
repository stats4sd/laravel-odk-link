<?php

namespace Stats4sd\OdkLink\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Http\Controllers\Operations\Concerns\HasForm;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;

trait ReviewOperation
{

    use HasForm;

    protected function setupReviewRoutes(string $segment, string $routeName, string $controller): void
    {
        $this->formRoutes(
            operationName: 'review',
            routesHaveIdSegment: true,
            segment: $segment,
            routeName: $routeName,
            controller: $controller
        );
    }

    protected function setupReviewDefaults(): void
    {
        $this->formDefaults(operationName: 'Review');

        $this->crud->setupDefaultSaveActions();
    }

    public function getReviewForm(int $id = null)
    {

        $entry = $this->crud->getCurrentEntry();

        if ($entry?->odk_error) {
            \Alert::add('danger', 'Your XLS Form file has one or more errors. Please review the file and upload a revised version before continuing the review.')->flash();

            return redirect(backpack_url("xlsform-template/{$entry?->id}/edit"));
        }

        $id = $this->crud->getCurrentEntryId() ?? $id;
        // get the info for that entry

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        $this->crud->removeSaveActions(['save_and_edit', 'save_and_show', 'save_and_new', 'save_and_preview']);

        return $this->formView($id);
    }

    public function postReviewForm()
    {
        $request = request();
        $entry = XlsformTemplate::find($this->crud->getCurrentEntryId());

        //handle media file attachments


        $form = $request->all();

        $files = $request->allFiles();



        if(array_key_exists('requiredFixedMedia', $files)) {
            $fixedMedia = $form['requiredFixedMedia'];

            foreach($fixedMedia as $index => $fixedMediaItem) {

                $requiredMediaItem = $entry->requiredFixedMedia()->find($fixedMediaItem['id']);

                $requiredMediaItem->addMediaFromRequest("requiredFixedMedia.{$index}.file_upload")
                ->toMediaLibrary();

                $requiredMediaItem->attachment()->associate($requiredMediaItem->getMedia()->first());
                $requiredMediaItem->save();
            }



        }

        dd('??', $files);


    }


}
