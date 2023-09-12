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

    protected $casts = [
        'is_static' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleting(function (RequiredMedia $requiredMedia) {
            $requiredMedia->getMedia()
                ->each(fn($media) => $requiredMedia->deleteMedia($media));
        });

        static::saved(function(RequiredMedia $requiredMedia) {

            // update related xlsform template to set draft_needs_updating to true
            $requiredMedia->xlsformTemplate->updateQuietly(
                ['draft_needs_updating' => true,]
            );

        });
    }


    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia();
    }

    public function xlsformTemplate(): BelongsTo
    {
        return $this->belongsTo(XlsformTemplate::class);
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
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
