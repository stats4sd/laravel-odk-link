<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Stats4sd\FileUtil\Models\Traits\HasUploadFields;

class XlsformTemplate extends Model
{
    use CrudTrait;
    use HasFactory;
    use HasUploadFields;

    protected $table = 'xlsform_templates';
    protected $guarded = ['id'];
    protected $casts = [
        'media' => 'array',
        'csv_lookups' => 'array',
    ];

    public function setXlsfileAttribute($value): void
    {
        $this->uploadFileWithNames($value, 'xlsfile', config('odk-link.xlsforms.storage_disk'), '');
    }

    public function setMediaAttribute($value): void
    {
        $this->uploadMultipleFilesWithNames($value, 'media', config('odk-link.xlsforms.storage_disk'), '');
    }

    // TODO: check if this still works after adding XlsformVersion...
    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(Submission::class, Xlsform::class);
    }

    public function xlsforms(): HasMany
    {
        return $this->hasMany(Xlsform::class);
    }
}
