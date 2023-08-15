<?php


namespace Stats4sd\OdkLink\Models;

use Illuminate\Database\Eloquent\Model;
use Stats4sd\OdkLink\Traits\HasXlsforms;
use Stats4sd\OdkLink\Traits\IsOdkEntity;


/**
 * A model to represent the "platform" itself. This way, the platform can be an XLSform Owner in the same way that users or teams can.
 * On platform startup, a platform entry and an ODK project is created for the platform itself. This project is only used for drafts of XLSform templates, for testing purposes. No live data collection should ever happen through this project.
 */
class Platform extends Model
{
    use HasXlsforms;

    protected $table = 'platforms';
    protected $guarded = [];

    public function getNameAttribute(): string
    {
        return 'PLATFORM TEST PROJECT';
    }

}
