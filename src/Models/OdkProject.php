<?php

namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;

/**
 * An ODK Central Project is linked to a single owner. This class is only required to ensure we do not need to interfere with the main application's 'owner' tables.
 */
class OdkProject extends Model
{
    use CrudTrait;

    protected $fillable = ['id', 'name', 'archived', 'description', 'odk_user', 'odk_pass'];
    public $incrementing = false;
    public $keyType = 'integer';
    protected $table = 'odk_projects';

    public function owner(): MorphTo
    {
        return $this->morphTo(); // can be linked to any Model with the HasXlsforms trait.
    }

    public function appUsers(): HasMany
    {
        return $this->hasMany(AppUser::class);
    }

    public function getOdkLink()
    {
        return config('odk-link.odk.url')."/#/projects/".$this->id;
    }

    // TODO: is this redundant? It's certainly not normalised SQL, as in theory we can get to Xlsforms via the owner, but we don't know the model type of the owner, so it's easier to add odk_project_id to the xlsforms table and add this relationship.
    public function xlsforms(): HasMany
    {
        return $this->hasMany(Xlsform::class);
    }



}
