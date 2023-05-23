<?php


namespace Stats4sd\OdkLink\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stats4sd\FileUtil\Models\Traits\HasUploadFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class XlsformSubject extends Model
{
    use CrudTrait;
    use HasFactory;
    use HasUploadFields;

    protected $table = 'xlsform_subjects';
    protected $guarded = ['id'];

    public function xlsformTemplates(): HasMany
    {
        return $this->hasMany(XlsformTemplate::class);
    }

}
