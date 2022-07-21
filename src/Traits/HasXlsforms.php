<?php

namespace Stats4sd\OdkLink\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stats4sd\OdkLink\Models\Xlsform;

trait HasXlsforms
{
    // Used as the human-readable label for the owners of forms. Uses the same variable name that some Laravel Backpack fields expect (e.g. Relationship)
    // Xls Form titles are in the format `$owner->$nameAttribute . '-' . $xlsform->title`
    public string $identifiableAttribute = 'name';

    public function getNameAttribute(): string
    {
        return $this->identifiableAttribute;
    }

    public function xlsforms(): MorphMany
    {
        return $this->morphMany(Xlsform::class, 'owner');
    }


}
