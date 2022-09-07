<?php

namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An ODK Central Project is linked to a single owner. This class is only required to ensure we do not need to interfere with the main application's 'owner' tables.
 */
class OdkProject extends Model
{
    use CrudTrait;

    protected $fillable = ['id', 'name', 'archived', 'description'];
    protected $table = 'odk_projects';

    public function owner(): MorphTo
    {
        return $this->morphTo(); // can be linked to any Model with the HasXlsforms trait.
    }

}
