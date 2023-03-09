<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Stats4sd\OdkLink\Exports\OdkSubmissionContentExport;
use Stats4sd\OdkLink\Jobs\UpdateXlsformTitleInFile;
use App\Models\User;
use Stats4sd\OdkLink\OdkLink;
use Stats4sd\OdkLink\Services\OdkLinkService;

class Xlsform extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = [];
    protected $table = 'xlsforms';

    public $appends = [
        'title',
        'draft_qr_code_string',
        'current_version',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        parent::booted();

        // when the model is created;
        static::saved(function ($xlsform) {

            // copy the xlsfile from the template and update the title and id:
            if (!$xlsform->xlsfile) {
                $xlsform->updateXlsfileFromTemplate();
            }

            // if the odk_project is not set, set it based on the given owner:
            $xlsform->odk_project_id = $xlsform->owner->odkProject->id;
            $xlsform->saveQuietly();
        });


    }

    public function scopeOwned($builder)
    {
        if (Auth::check()) {
            $builder->where(function ($query) {
                $query->whereHas('owner', function (Builder $query) {

                    // is the xlsform owned by the logged in user?
                    if (is_a($query->getModel(), User::class)) {
                        $query->where('users.id', Auth::id());
                    } else {
                        // is the xlsform owned by a team/group other entity that the logged in user is linked to?
                        $query->whereHas('users', function ($query) {
                            $query->where('users.id', Auth::id());
                        });
                    }
                });
            });
        }
    }

    // If no title is given, add a default title by combining the owner name and template title.
    public function title(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if ($value) {
                    return $value;
                }
                return $this->xlsformTemplate ?
                    $this->owner?->{$this->getOwnerIdentifierAttributeName()} . ' - ' . $this->xlsformTemplate->title
                    : '';
            },
        );
    }

    // Get an xlsformId string that is both human-readable and guaranteed to be unique within the platform
    public function xlsform_id(): Attribute
    {
        return new Attribute(
            get: function () {
                return str($this->title)->slug() . '_' . $this->id;
            }
        );
    }

    public function owned_by_name(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->owner->{$this->getOwnerIdentifierAttributeName()} ?? '';
            }
        );
    }


    // Get the identifiableAttribute of the owner
    public function getOwnerIdentifierAttributeName(): ?string
    {
        return $this->owner?->identifiableAttribute;
    }


    public function getCurrentVersionAttribute()
    {
        return $this->xlsformVersions()
            ->where('active', 1)
            ->orderBy('created_at')
            ->get()
            ->last()?->version ?? null;
    }


    /*
     * Gets the direct url to the form on the ODK Aggregate Service
     */
    public function getOdkLink(): ?string
    {
        $appends = !$this->is_active ? '/draft' : '';

        return config('odk-link.odk.url') . "/#/projects/" . $this->owner->odkProject->id . '/forms/' . $this->odk_id . $appends;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // any model with the HasXlsforms trait can be linked to this model. That model then can "own" forms.-
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }


    public function xlsformTemplate(): BelongsTo
    {
        return $this->belongsTo(XlsformTemplate::class);
    }

    public function xlsformVersions(): HasMany
    {
        return $this->hasMany(XlsformVersion::class);
    }

    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(Submission::class, XlsformVersion::class);
    }

    public function updateXlsfileFromTemplate(): void
    {
        $filePath = 'xlsforms/' . $this->id . '/' . $this->xlsformTemplate->xlsfile;

        Storage::disk(config('odk-link.storage.xlsforms'))->copy($this->xlsformTemplate->xlsfile, $filePath);

        $this->xlsfile = $filePath;
        $this->saveQuietly();
        UpdateXlsformTitleInFile::dispatchSync($this);
    }

    /**
     * Method to retrieve the encoded settings for the current draft version on ODK Central
     * @throws \JsonException
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

    public function deployDraft(OdkLinkService $service): void
    {

        $odkXlsFormDetails = $service->createDraftForm($this);

        $this->updateQuietly([
            'odk_id' => $odkXlsFormDetails['xmlFormId'],
            'odk_draft_token' => $odkXlsFormDetails['draftToken'],
            'odk_version_id' => $odkXlsFormDetails['version'],
            'has_draft' => true,
            'enketo_draft_url' => $odkXlsFormDetails['enketoId'],
        ]);
    }

    public function deployLive(OdkLinkService $service): void
    {

    }

    public function exportSubmissionData()
    {
        return Excel::download(new OdkSubmissionContentExport($this), 'test.xlsx');
    }
}
