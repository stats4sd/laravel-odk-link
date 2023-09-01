<?php

namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class RequiredMedia extends Model implements HasMedia
{
    use CrudTrait;
    use InteractsWithMedia;

    protected $table = 'required_media';

    protected $guarded = [];
    protected $appends = ['has_media'];

    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia();
    }

    public function xlsformTemplate(): BelongsTo
    {
        return $this->belongsTo(XlsformTemplate::class);
    }

    public function attachment(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDatasetAttribute()
    {
        if($this->attachment_type === "Stats4sd\\OdkLink\\Models\\Dataset") {
            return $this->attachment;
        }

        return null;
    }

    public function getImageUrlAttribute()
    {
        if($this->attachment_type === "Spatie\MediaLibrary\MediaCollections\Models\Media") {
            return $this->attachment->getUrl();
        }

        return '';
    }



}
