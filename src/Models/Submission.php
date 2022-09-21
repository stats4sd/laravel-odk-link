<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'submissions';
    public $incrementing = false;
    public $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'content' => 'array',
        'errors' => 'array',
        'entries' => 'array',
    ];


    // $this->entries is an array of every Model entry created as a result of processing this submission.
    // This helper function makes it easy to update this array.
    public function addEntry(String $model, array $ids): void
    {
        $value = $this->entries;

        if ($value && array_key_exists($model, $value)) {
            $value[$model] = array_merge($value[$model], $ids);
        } else {
            $value[$model] = $ids;
        }

        $this->entries = $value;
        $this->save();
    }

    public function getXlsformTitleAttribute()
    {
        return $this->xlsformVersion->xlsform->title;
    }

    public function xlsformVersion(): BelongsTo
    {
        return $this->belongsTo(XlsformVersion::class);
    }
}
