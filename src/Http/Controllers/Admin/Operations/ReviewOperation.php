<?php

namespace Stats4sd\OdkLink\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Http\Controllers\Operations\Concerns\HasForm;

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
    }

    public function getReviewForm(int $id = null)
    {

        $entry = $this->crud->getCurrentEntry();

        if ($entry?->odk_error) {
            \Alert::add('danger', 'Your XLS Form file has one or more errors. Please review the file and upload a revised version before continuing the review.')->flash();

            return redirect(backpack_url("xlsform-template/{$entry?->id}/edit"));
        }

        return $this->formView($id);
    }


}
