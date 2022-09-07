<?php

namespace Stats4sd\OdkLink\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Services\OdkLinkService;

/**
 * Add this trait to any model that will 'own' xlsforms. Common examples include:
 * - Users (an individual user could own forms)
 * - Teams (a team or group of users could own forms, where each user within the team has access to all team forms).
 */
trait HasXlsforms
{
    protected static function booted()
    {
        parent::booted();

        $odkLinkService = app()->make(OdkLinkService::class);

        // when the model is created; automatically create an associated project on ODK Central;
        static::created(function ($owner) use ($odkLinkService) {
            $odkProject = $odkLinkService->createProject($owner->name);
            $owner->odkProject()->create([
                'id' => $odkProject['id'],
                'name' => $odkProject['name'],
                'archived' => $odkProject['archived'],
            ]);
        });
    }

    // Used as the human-readable label for the owners of forms. Uses the same variable name that some Laravel Backpack fields expect (e.g. Relationship)
    // Xls Form titles are in the format `$owner->$nameAttribute . '-' . $xlsform->title`
    public string $identifiableAttribute = 'name';

    public function xlsforms(): MorphMany
    {
        return $this->morphMany(Xlsform::class, 'owner');
    }

    // Private templates are owned by a single form owner.
    // All owners have access to all public templates (templates where available = 1)
    public function xlsformTemplates(): MorphMany
    {
        return $this->morphMany(XlsformTemplate::class, 'owner');
    }

    public function odkProject(): MorphOne
    {
        return $this->morphOne(OdkProject::class, 'owner');
    }


}
