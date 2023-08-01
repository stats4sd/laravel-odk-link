<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Dflydev\DotAccessData\Data;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Stats4sd\OdkLink\Traits\HasXlsforms;


/**
 *
 */
class Entity extends Model
{
    use HasXlsforms;
    use CrudTrait;

    protected $table = 'entities';
    protected $guarded = [];


    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // Is the entity linked to an existing entry in a database table?
    // E.g., if the platform has a "farms" table, and you want to include a farms dataset into the ODK system, there should be one entity entry per farm entry.
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function values(): HasMany
    {
        return $this->hasMany(EntityValue::class, 'entity_id');
    }

    public function datasetVariables(): BelongsToMany
    {
        return $this->belongsToMany(DatasetVariable::class, 'entity_values')
            ->using(EntityValue::class)
            ->withPivot('value');
    }

    public function getVariableList(): Collection
    {
        return $this->datasetVariables?->pluck('name') ?? collect([]);
    }

    /*
     * By default, check linked key-value table;
     */
    public function getAttribute($key)
    {

        // if the default getAttribute() returns something, great! Do that
        if ($value = parent::getAttribute($key)) {
            return $value;
        }


        /*
         * If the requested attribute is in the dataset variables list, check the values() relationship
         */
        if ($this->getVariableList()->contains($key)) {
            return $this->values()->whereHas('datasetVariable', function (Builder $query) use ($key) {
                $query->where('dataset_variables.name', $key);
            })->first()?->value;
        }

        /*
         * Otherwise, attempt to defer to the linked model:
         */
        return $this->model->getAttribute($key);

    }

}
