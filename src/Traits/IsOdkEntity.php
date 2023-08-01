<?php

namespace Stats4sd\OdkLink\Traits;

use Backpack\CRUD\app\Models\Traits\HasIdentifiableAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stats4sd\OdkLink\Models\Dataset;
use Stats4sd\OdkLink\Models\Entity;
use Stats4sd\OdkLink\Models\OdkDataset;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Services\OdkLinkService;


trait IsOdkEntity
{
    use HasIdentifiableAttribute;

    protected static function booted()
    {
        static::created(function (Model $item) {

            // Find or create a Dataset entry for this model type
            $dataset = Dataset::firstOrCreate(
                ['entity_model' => self::class],
                ['name' => $item->getTable()]
            );

            $item->entity()->create([
                'name' => $item->{$item->identifiableAttribute()},
                'dataset_id' => $dataset->id,

            ]);
        });
    }

    public function entity(): MorphOne
    {
        return $this->morphOne(Entity::class, 'model');
    }

}
