<?php


namespace Stats4sd\OdkLink\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use JsonException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stats4sd\FileUtil\Models\Traits\HasUploadFields;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Stats4sd\OdkLink\Jobs\UpdateXlsformTitleInFile;
use Stats4sd\OdkLink\Models\Interfaces\WithXlsFormDrafts;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Symfony\Contracts\Service\Attribute\Required;

class XlsformTemplate extends Model implements HasMedia, WithXlsFormDrafts
{
    use CrudTrait;
    use HasFactory;
    use HasUploadFields;
    use InteractsWithMedia;

    protected $table = 'xlsform_templates';
    protected $guarded = ['id'];
    protected $casts = [
        'media' => 'array',
        'csv_lookups' => 'array',
        'schema' => 'collection',
    ];

    protected static function booted()
    {
        // on creating, push the new form to ODK Central
        static::created(function (XlsformTemplate $xlsformTemplate) {
            $odkLinkService = app()->make(OdkLinkService::class);
            $xlsformTemplate->owner()->associate(Platform::first());

            // update form title in xlsfile to match user-given title
            UpdateXlsformTitleInFile::dispatchSync($xlsformTemplate);

            // set the owner

            $xlsformTemplate->deployDraft($odkLinkService);
            $xlsformTemplate->getRequiredMedia($odkLinkService);
            $xlsformTemplate->saveQuietly();
        });
    }

    public function setXlsfileAttribute($value): void
    {
        $this->uploadFileWithNames($value, 'xlsfile', config('odk-link.storage.xlsforms'), '');
    }

    public function setCsvLookupsAttribute($value): void
    {
        // filter out any entries where mysql_name === null and csv_name === null
        // these are assumed to be unused entries where the user simply did not remove them.
        $this->attributes['csv_lookups'] = collect($value)->filter(fn($entry) => $entry['mysql_name'] && $entry['csv_name'])->toJson();
    }

    public function setMediaAttribute($value): void
    {

        if (is_array($value)) {
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

    public function xlsformSubject(): BelongsTo
    {
        return $this->belongsTo(XlsformSubject::class);
    }

    // A template is either available to everyone (owner_id === NULL), or is owned by a single entity.
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

        if (!$request) {
            return;
        }

        if (!is_array($this->{$attribute_name})) {
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


    /**
     * @throws RequestException
     */
    public function deployDraft(OdkLinkService $service): void
    {

        $odkXlsFormDetails = $service->createDraftForm($this);

        if ($odkXlsFormDetails) {
            $this->updateQuietly([
                'odk_id' => $odkXlsFormDetails['xmlFormId'],
                'odk_draft_token' => $odkXlsFormDetails['draftToken'],
                'odk_version_id' => $odkXlsFormDetails['version'],
                'has_draft' => true,
                'enketo_draft_url' => $odkXlsFormDetails['enketoId'],
            ]);
        }
    }

    /**
     * Method to retrieve the encoded settings for the current draft version on ODK Central
     * @throws JsonException
     */
    public function getDraftQrCodeStringAttribute(): ?string
    {
        if (!$this->has_draft) {
            return null;
        }

        $settings = [
            "general" => [
                "server_url" => config('odk-link.odk.base_endpoint') . "/test/{$this->odk_draft_token}/projects/{$this->owner->odkProject->id}/forms/{$this->odk_id}/draft",
                "form_update_mode" => "match_exactly",
            ],
            "project" => ["name" => "(DRAFT) " . $this->title, "icon" => "📝"],
            "admin" => ["automatic_update" => true],
        ];

        $json = json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return base64_encode(zlib_encode($json, ZLIB_ENCODING_DEFLATE));

    }

    public function getEnketoDraftUrlAttribute($value): ?string
    {
        if($value) {
            return $value;
        }

        // if there is no enketo url in the database, retrieve it from ODK Central
        $odkLinkService = app()->make(OdkLinkService::class);

        $this->updateDraftFormDetails($odkLinkService);

        return $this->attributes['enketo_draft_url'];

    }

    public function updateDraftFormDetails(OdkLinkService $odkLinkService)
    {
        $updated = $odkLinkService->getDraftFormDetails($this);

        $this->update([
            'odk_draft_token' => $updated['draftToken'],
            'enketo_draft_url' => $updated['enketoId'],
        ]);
    }


    public function getOdkLink(): ?string
    {
        return config('odk-link.odk.url') . "/#/projects/" . $this->owner->odkProject->id . "/forms/" . $this->odk_id . "/draft";
    }

    /** 1 entry created for each required item as given from ODK Central */
    public function requiredMedia(): HasMany
    {
        return $this->hasMany(RequiredMedia::class);
    }

    /** filtered Required Media to only show media with type "image", "video" or "audio" */
    public function requiredFixedMedia(): HasMany
    {
        return $this->hasMany(RequiredMedia::class)
            ->where('required_media.type', '!=', 'file');
    }

    public function requiredDataMedia(): HasMany
    {
        return $this->hasMany(RequiredMedia::class)
            ->where('required_media.type', '=', 'file');
    }

    public function attachedFixedMedia(): HasMany
    {
        return $this->hasMany(RequiredMedia::class)
            ->where('required_media.type', '!=', 'file')
            ->where('required_media.attachment_id', '!=', null);
    }

    public function attachedDataMedia(): HasMany
    {
        return $this->hasMany(RequiredMedia::class)
            ->where('required_media.type', '=', 'file')
            ->where('required_media.attachment_id', '!=', null);
    }

    public function getRequiredMedia(OdkLinkService $odkLinkService): void
    {
        $mediaItems = $odkLinkService->getRequiredMedia($this);

        foreach ($mediaItems as $mediaItem) {
            $this->requiredMedia()->updateOrCreate([
                'name' => $mediaItem['name'],
            ], [
                'type' => $mediaItem['type'],
                'exists_on_odk' => $mediaItem['exists'],
            ]);
        }
    }


    //  get the schema fields that are present in any groups or repeats;
    public function getStructureAttribute()
    {
        return $this->schema->filter(fn($item) => $item['type'] === 'structure')
            ->map(function($item) {
                $item['sub_items'] = $this->schema->filter(fn($subItem) => Str::contains($subItem['path'], $item['path']) && $subItem['path'] !== $item['path']);

                return $item;
            });
    }

    // get all schema fields that are on the 'root' of the structure. (i.e. fields that will appear in the main 'survey' tab in a combined download)
    public function getRootFieldsAttribute()
    {
        return $this->schema->filter(fn($item) => $item['type'] !== 'structure' && $item['path'] === "/{$item['name']}");
    }

    public function datasets(): BelongsToMany
    {
        return $this->belongsToMany(Dataset::class)
            ->withPivot([
                'is_root',
                'is_repeat',
                'structure_item'
            ]);
    }

    public function getRootDatasetIdAttribute()
    {
        return $this->datasets->filter(fn($dataset)  =>  $dataset->pivot->is_root)->first()?->id;
    }
}
