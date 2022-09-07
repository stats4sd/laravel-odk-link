<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
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
        $this->uploadFileWithNames($value, 'xlsfile', config('odk-link.storage.xlsforms'), '');
    }

    public function setMediaAttribute($value): void
    {

        if(is_array($value)){
            $value = json_encode($value);
        }

        $this->uploadMultipleFilesWithNames($value, 'media', config('odk-link.storage.xlsforms'), '');
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

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * CUSTOM IMPLEMENTATION // TODO: add this as an option into the File Utils package
     * Handle multiple file upload and DB storage:
     * - if files are sent
     *     - stores the files at the destination path
     *     - generates random names
     *     - stores the full path in the DB, as JSON array;
     * - if a hidden input is sent to clear one or more files
     *     - deletes the file
     *     - removes that file from the DB.
     *
     * @param ?string $value Value for that column sent from the input.
     * @param string $attribute_name Model attribute name (and column in the db).
     * @param string $disk Filesystem disk used to store files.
     * @param string $destination_path Path in disk where to store the files.
     */
    public function uploadMultipleFilesWithNames(?string $value, string $attribute_name, string $disk, string $destination_path): void
    {
        $request = request();

        if(!$request) {
            return;
        }

        if (! is_array($this->{$attribute_name})) {
            $attribute_value = json_decode($this->{$attribute_name}, true) ?? [];
        } else {
            $attribute_value = $this->{$attribute_name};
        }
        $files_to_clear = $request->get('clear_' . $attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            foreach ($files_to_clear as $key => $filename) {
                Storage::disk($disk)->delete($filename);
                $attribute_value = Arr::where($attribute_value, function ($value, $key) use ($filename) {
                    return $value !== $filename;
                });
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name)) {
            foreach ($request->file($attribute_name) as $file) {
                if ($file->isValid()) {

                    // 2. Move the new file to the correct path
                    $file_path = $file->storeAs($destination_path, $file->getClientOriginalName(), $disk);

                    // 3. Add the public path to the database
                    $attribute_value[] = $file_path;
                }
            }
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }
}
