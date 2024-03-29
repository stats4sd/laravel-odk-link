<?php


namespace Stats4sd\OdkLink\Models;

use App\Models\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Submission extends Model implements HasMedia
{
    use CrudTrait;
    use HasFactory;
    use InteractsWithMedia;


    protected $table = 'submissions';
    protected $guarded = ['id'];
    protected $casts = [
        'content' => 'array',
        'errors' => 'array',
        'entries' => 'array',
    ];

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('owned', function (Builder $query) {
            if (Auth::check() && !Auth::user()?->hasRole(config('odk-link.roles.xlsform-admin'))) {
                $query->where(function (Builder $query) {
                    $query->whereHas('xlsformVersion', function (Builder $query) {
                        $query->whereHas('xlsform', function (Builder $query) {
                            $query->whereHas('owner', function (Builder $query) {

                                //if xlsforms are owned by a user, return the user's forms directly.

                                if (is_a($query->getModel(), User::class)) {
                                    $query->where('users.id', Auth::id());
                                } else {
                                    // is the xlsform owned by a team/group that the logged-in user is linked to?
                                    $query->whereHas('users', function ($query) {
                                        $query->where('users.id', Auth::id());
                                    });
                                }
                            });
                        });
                    });
                });
            }
        });
    }

    public function scopeOwned(Builder $builder): void
    {

    }


    // $this->entries is an array of every Model entry created as a result of processing this submission.
    // This helper function makes it easy to update this array.
    public function addEntry(string $model, array $ids): void
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
