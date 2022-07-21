<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Xlsform extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = [];
    protected $table = 'xlsforms';

    protected $with = ['xlsform_template'];

    public $appends = [
        'title',
        'records',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute(): string
    {
        $nameAttribute = $this->owner->getNameAttribute();

        return $this->owner ? $this->team->$nameAttribute.' - '.$this->xlsform->title : '';
    }

    public function getRecordsAttribute(): int
    {
        return $this->submissions->count();
    }

    public function getCurrentVersion()
    {
        return $this->xlsformVersions()
            ->where('active', 1)
            ->sort_by('created_at')
            ->last()
            ->version;
    }

    /*
     * Gets the direct url to the form on the ODK Aggregate Service
     */
    public function getOdkLink(): ?string
    {
        if(config('odk-link.odk.aggregator') === "kobotoolbox") {
            return config('odk-link.odk.base_endpoint') . "/#/forms/" . $this->odk_id;
        }

        if(config('odk-link.odk.aggregator') === "odk-central") {
            return config('odk-link.odk.base_endpoint') . "/#/projects/" . $this->owner->odk_id . '/forms/' . $this->odk_id;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // any model with the HasXlsforms trait can be linked to this model. That model then can "own" forms.-
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }


    public function xlsformTemplate(): BelongsTo
    {
        return $this->belongsTo(XlsformTemplate::class);
    }

    public function xlsformVersions(): HasMany
    {
        return $this->hasMany(XlsformVersion::class);
    }

    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(Submission::class, XlsformVersion::class);
    }
}
