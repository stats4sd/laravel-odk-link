<?php

namespace Stats4sd\OdkLink\Services;

use _PHPStan_9a6ded56a\React\Http\Message\ResponseException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Psr\SimpleCache\InvalidArgumentException;
use Stats4sd\OdkLink\Exports\SqlViewExport;
use Stats4sd\OdkLink\Jobs\UpdateXlsformTitleInFile;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformVersion;


/**
 * All ODK Aggregation services should be able to handle ODK forms, so this interface should always be used.
 *
 */
class OdkLinkService
{
    public function __construct(protected string $endpoint)
    {
    }

    /**
     * Creates a new session + auth token for communication with the ODK Central server
     * @return string $token
     */
    public function authenticate(): string
    {
        // if a token exists in the cache, return it. Otherwise, create a new session and store the token.
        return Cache::remember('odk-token', now()->addHours(20), function () {

            $response = Http::post("{$this->endpoint}/sessions", [
                "email" => config("odk-link.odk.username"),
                "password" => config("odk-link.odk.password"),
            ])
                ->throw()
                ->json();

            return $response['token'];

        });

    }


    /**
     * Returns the Url that the user can use to view the form in the browser.
     * @param Xlsform $xlsform
     * @return string $url
     */
    public function getFormUrl(Xlsform $xlsform): string
    {

    }

    /**
     * Returns the API url of the specific form.
     * @param Xlsform $xlsform
     * @return string $url
     */
    public function getFormApiUrl(Xlsform $xlsform): string
    {

    }

    /**
     * Creates a new project in ODK Central
     * @param string $name
     * @return array $projectInfo
     * @throws RequestException
     */
    public function createProject(string $name): array
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post("{$this->endpoint}/projects", [
                'name' => $name,
            ])
            ->throw()
            ->json();
    }

    /**
     * Updates a project name
     * @param OdkProject $odkProject
     * @param string $newName
     * @return array $projectInfo
     * @throws RequestException
     */
    public function updateProject(OdkProject $odkProject, string $newName): array
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post("{$this->endpoint}/projects/$odkProject->id", [
                'name' => $newName,
            ])
            ->throw()
            ->json();
    }

    /**
     * Archives a project
     * @param OdkProject $odkProject
     * @return array $success
     * @throws RequestException
     */
    public function archiveProject(OdkProject $odkProject): array
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post("{$this->endpoint}/projects/$odkProject->id", [
                'name' => $odkProject->name,
                'archived' => true,
            ])
            ->throw()
            ->json();
    }

    /**
     * Creates a new (draft) form.
     * If the form is not already deployed, it will create a new form instance on ODK Central.
     * If the form is already deployed, it will push the current XLSfile as a new draft to the existing form.
     * @param Xlsform $xlsform
     * @return array $xlsformDetails
     * @throws RequestException
     */
    public function createDraftForm(Xlsform $xlsform): array
    {
        $token = $this->authenticate();

        $file = file_get_contents(Storage::disk(config('odk-link.storage.xlsforms'))->path($xlsform->xlsfile));

        $url = "{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms?ignoreWarnings=true&publish=false";

        // if the form is already on ODK Central, post to /forms/{id}/draft endpoint. Otherwise, post to /forms endpoint to create an entirely new form.
        if ($xlsform->odk_id) {
            $url = "{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}/draft?ignoreWarnings=true";
        }

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'X-XlsForm-FormId-Fallback' => Str::slug($xlsform->title) . ".xlsx",
            ])
            ->withBody($file, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->post($url)
            ->throw()
            ->json();

        // when creating a new draft for an existing form, the full form details are not returned. In this case, the $xlsform record can remain unchanged
        if (isset($response['xmlFormId'])) {
            $xlsform->update(['odk_id' => $response['xmlFormId']]);
        }

        // deploy media files
        $this->uploadMediaFileAttachments($xlsform);


        return $this->getDraftFormDetails($xlsform);
    }

    /**
     * Gets the draft form details for a given xlsform
     * @param Xlsform $xlsform
     * @return array
     * @throws RequestException
     */
    public function getDraftFormDetails(Xlsform $xlsform): array
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}/draft")
            ->throw()
            ->json();
    }

    /**
     * Uploads all media files for an XLSform to ODK Central - both static files and dyncsv files
     * @param Xlsform $xlsform
     * @return bool $success
     * @throws RequestException
     */
    public function uploadMediaFileAttachments(Xlsform $xlsform): bool
    {
        // static files
        $files = $xlsform->xlsformTemplate->media;

        if ($files && count($files) > 0) {

            foreach ($files as $file) {
                $this->uploadSingleMediaFile($xlsform, $file);
            }

        }
        // dynamic files
        $csv_lookups = $xlsform->xlsformTemplate->csv_lookups;


        if ($csv_lookups && count($csv_lookups) > 0) {

            foreach ($csv_lookups as $lookup) {

                $this->uploadSingleMediaFile(
                    $xlsform,
                    $this->createCsvLookupFile($xlsform, $lookup),
                );

            }
        }

        return true;

    }

    /**
     * Uploads a single media file to the given xlsform
     * @param Xlsform $xlsform
     * @param string $filePath
     * @return array
     * @throws RequestException
     */
    public function uploadSingleMediaFile(Xlsform $xlsform, string $filePath): array
    {
        $token = $this->authenticate();
        $file = file_get_contents(Storage::disk(config('odk-link.storage.xlsforms'))->path($filePath));

        $mimeType = mime_content_type(Storage::disk(config('odk-link.storage.xlsforms'))->path($filePath));
        $fileName = collect(explode("/", $filePath))->last();

        try {

            return Http::withToken($token)
                ->contentType($mimeType)
                ->withBody($file, $mimeType)
                ->post("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}/draft/attachments/{$fileName}")
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            if ($exception->getCode() === 404) {
                abort(500, 'The file ' . $fileName . ' is not an expected file name for this ODK form template. Please review the form and check which media files are expected');
            }
        }
    }

    /**
     * Publishes the current draft form so it is available for live data collection
     * @param Xlsform $xlsform
     * @return XlsformVersion $xlsformVersion
     */
    public function publishForm(Xlsform $xlsform): XlsformVersion
    {

        $token = $this->authenticate();

        // create a new version locally
        $version = 1;

        // if there is an existing version; increment the version number;
        if ($xlsform->xlsformVersions()->count() > 0) {
            $version = $xlsform->xlsformVersions()->orderBy('version', 'desc')->first()->version + 1;
        }

        dump('running publish post');
        Http::withToken($token)
            ->post("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}/draft/publish?version={$version}")
            ->throw()
            ->json();

        // TODO: move all of this into some form of XlsformVersion handler!

        // deactivate all other versions;
        $xlsform->xlsformVersions()->update([
            'active' => false,
        ]);

        // base xlsfile name
        $fileName = collect(explode("/", $xlsform->xlsfile))->last();

        // copy xlsform file to store linked to this version forever
        Storage::disk(config('odk-link.storage.xlsforms'))
            ->copy(
                $xlsform->xlsfile,
                "xlsforms/{$xlsform->id}/versions/{$version}/{$fileName}"
            );

        // create new active version with latest version number;
        return $xlsform->xlsformVersions()->create([
            'version' => $version,
            'xlsfile' => "xlsforms/{$xlsform->id}/versions/{$version}/{$fileName}",
            'odk_version' => $version,
            'active' => true,
        ]);

    }

    /**
     * Archives a form to prevent further data collection
     * @param Xlsform $xlsform
     * @return array $xlsformDetails
     */
    public function archiveForm(Xlsform $xlsform): bool
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->patch("{$this->endpoint}/projects{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}", [
                'state' => 'closed',
            ])
            ->throw()
            ->json();

    }

    /**
     * Generates a lookup file for a specific xlsform.
     * @param Xlsform $xlsform
     * @param mixed $lookup
     * @return void
     */
    private function generateLookupFile(Xlsform $xlsform, mixed $lookup)
    {
    }

    /**
     * Creates a new csv lookup file from the database;
     * @param Xlsform $xlsform
     * @param mixed $lookup
     * @return string
     */
    private function createCsvLookupFile(Xlsform $xlsform, mixed $lookup): string
    {

        $filePath = 'xlsforms' . $xlsform->id . '/' . $lookup['csv_name'] . ".csv";

        if ($lookup['per_owner'] === "1") {
            $owner = $xlsform->owner;
        } else {
            $owner = null;
        }


        Excel::store(
            new SqlViewExport($lookup['mysql_name'], $owner),
            $filePath,
            config('odk-link.storage.xlsforms')
        );

        // If the csv file is used with "select_one_from_external_file" (or multiple) it must not have any enclosure characters:
        if (isset($lookup['external_file']) && $lookup['external_file'] === "1") {
            $contents = Storage::disk(config('odk-link.storage.xlsforms'))->get($filePath);
            $contents = Str::of($contents)->replace('"', '');

            Storage::disk(config('odk-link.storage.xlsforms'))->put($filePath, $contents);
        }

        return $filePath;
    }
}

