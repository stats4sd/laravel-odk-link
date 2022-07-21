<?php

namespace Stats4sd\OdkLink\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class XlsformVersion extends Model
{

    use HasFactory;

    protected $table = "xlsform_versions";
    protected $guarded = [];


    public function getTitleAttribute(): string
    {
        return $this->team ? $this->team->name.' - '.$this->xlsform->title : '';
    }

    public function getRecordsAttribute(): int
    {
        return $this->submissions->count();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function xlsform(): BelongsTo
    {
        return $this->belongsTo(Xlsform::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
