<?php


namespace Stats4sd\OdkLink\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stats4sd\OdkLink\Traits\HasXlsforms;


/**
 * A model to represent the "platform" itself. This way, the platform can be an XLSform Owner in the same way that users or teams can.
 * On platform startup, a platform entry and an ODK project is created for the platform itself. This project is only used for drafts of XLSform templates, for testing purposes. No live data collection should ever happen through this project.
 */
class Dataset extends Model
{
    use HasXlsforms;

    protected $table = 'datasets';
    protected $guarded = [];

    public function requiredMedia(): MorphMany
    {
        return $this->morphMany(RequiredMedia::class, 'attachment');
    }

    public function odkDatasets(): HasMany
    {
        return $this->hasMany(OdkDataset::class);
    }

}
