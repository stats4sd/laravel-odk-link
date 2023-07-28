<?php


namespace Stats4sd\OdkLink\Models;

use Dflydev\DotAccessData\Data;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stats4sd\OdkLink\Traits\HasXlsforms;


/**
 * A model to represent the "platform" itself. This way, the platform can be an XLSform Owner in the same way that users or teams can.
 * On platform startup, a platform entry and an ODK project is created for the platform itself. This project is only used for drafts of XLSform templates, for testing purposes. No live data collection should ever happen through this project.
 */
class OdkDataset extends Model
{
    use HasXlsforms;

    protected $table = 'datasets';
    protected $guarded = [];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function odkProject(): BelongsTo
    {
        return $this->belongsTo(OdkProject::class);
    }

}
