<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;
use Stats4sd\OdkLink\Traits\HasXlsforms;


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
