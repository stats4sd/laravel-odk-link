<?php


namespace Stats4sd\OdkLink\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;

class AppUser extends Model
{
    use CrudTrait;

    protected $table = 'app_users';
    protected $guarded = [];


    public function odkProject(): BelongsTo
    {
        return $this->belongsTo(OdkProject::class);
    }

    public function xlsforms(): BelongsToMany
    {
        return $this->belongsToMany(Xlsform::class, 'app_user_assignments');
    }

    /**
     * Method to retrieve the encoded settings to create a QR code that allows access to the entire project.
     * @throws \JsonException
     */
    public function getQrCodeStringAttribute(): ?string
    {
        $settings = [
            "general" => [
                "server_url" => config('odk-link.odk.base_endpoint') . "/key/{$this->token}/projects/{$this->odkProject->id}",
            ],
            "project" => ["name" => $this->odkProject->name],
            "admin" => ["automatic_update" => true],
        ];

        $json = json_encode($settings, JSON_UNESCAPED_SLASHES);

        return base64_encode(
            zlib_encode(
                $json,
                ZLIB_ENCODING_DEFLATE
            )
        );


    }


}
