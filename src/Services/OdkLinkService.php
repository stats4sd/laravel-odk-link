<?php

namespace Stats4sd\OdkLink\Services;

use _PHPStan_9a6ded56a\React\Http\Message\ResponseException;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Psr\SimpleCache\InvalidArgumentException;
use SaintSystems\OData\ODataClient;
use Stats4sd\OdkLink\Exports\SqlViewExport;
use Stats4sd\OdkLink\Jobs\UpdateXlsformTitleInFile;
use Stats4sd\OdkLink\Models\AppUser;
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

    public function createProjectAppUser(OdkProject $odkProject): array
    {
        $token = $this->authenticate();


        // create new app-user
        $userResponse = Http::withToken($token)
            ->post("{$this->endpoint}/projects/{$odkProject->id}/app-users", [
                'displayName' => 'All Forms - ' . $odkProject->owner->name . " - " . $odkProject->appUsers()->count()+1,
            ])
            ->throw()
            ->json();

        // assign user to all the forms in the project
        Http::withToken($token)
            ->post("{$this->endpoint}/projects/{$odkProject->id}/assignments/manager/{$userResponse['id']}")
            ->throw()
            ->json();

        return $userResponse;

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

//        // create a new version locally
//        $version = 1;
//
//        // if there is an existing version; increment the version number;
//        if ($xlsform->xlsformVersions()->count() > 0) {
//            $version = $xlsform->xlsformVersions()->orderBy('version', 'desc')->first()->version + 1;
//        }

        Http::withToken($token)
            ->post("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}/draft/publish?version=" . Carbon::now()->toDateTimeString())
            ->throw()
            ->json();

        // Get the version information;
        $formDetails = Http::withToken($token)
            ->get("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}")
            ->throw()
            ->json();

        $version = $formDetails['version'];

        if ($formDetails['state'] !== 'open') {
            $formDetails = $this->unArchiveForm($xlsform);
        }


        // TODO: move all of this into some form of XlsformVersion handler!
        // deactivate all other versions;
        $xlsform->xlsformVersions()->update([
            'active' => false,
        ]);
        $xlsformVersion = $this->createNewVersion($xlsform, $version);

        $xlsform->update([
            'has_draft' => false,
            'is_active' => true,
            'odk_version_id' => $xlsformVersion->version,
        ]);
        $xlsform->save();

        return $xlsformVersion;

    }

    /**
     * Archives a form to prevent further data collection
     * @param Xlsform $xlsform
     * @return array $xlsformDetails
     */
    public function archiveForm(Xlsform $xlsform): array
    {
        $token = $this->authenticate();

        $result = Http::withToken($token)
            ->patch("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}", [
                'state' => 'closed',
            ])
            ->throw()
            ->json();

        $xlsform->update([
            'is_active' => false,
        ]);

        return $result;

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

    public function test()
    {

        $data = Http::withToken($this->authenticate())
            ->get("{$this->endpoint}/projects/24/app-users")
            ->throw()
            ->json();

        AppUser::create(
            [
                'id' => $data[0]['id'],
                'odk_project_id' => $data[0]['projectId'],
                'type' => $data[0]['type'],
                'display_name' => $data[0]['displayName'],
                'token' => $data[0]['token'],
            ]
        );

        return 'hi';
    }

    public function unArchiveForm(Xlsform $xlsform)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->patch("{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}", [
                'state' => 'open',
            ])
            ->throw()
            ->json();
    }

    /**
     * @param Xlsform $xlsform
     * @param mixed $version
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createNewVersion(Xlsform $xlsform, mixed $version): \Illuminate\Database\Eloquent\Model
    {
        // base xlsfile name
        $fileName = collect(explode("/", $xlsform->xlsfile))->last();
        $versionSlug = Str::slug($version);

        // copy xlsform file to store linked to this version forever
        Storage::disk(config('odk-link.storage.xlsforms'))
            ->copy(
                $xlsform->xlsfile,
                "xlsforms/{$xlsform->id}/versions/{$versionSlug}/{$fileName}"
            );

        // create new active version with latest version number;
        $xlsformVersion = $xlsform->xlsformVersions()->create([
            'version' => $version,
            'xlsfile' => "xlsforms/{$xlsform->id}/versions/{$versionSlug}/{$fileName}",
            'odk_version' => $version,
            'active' => true,
        ]);
        return $xlsformVersion;
    }


    public function getSubmissions(Xlsform $xlsform)
    {
        $token = $this->authenticate();
        $oDataServiceUrl = "{$this->endpoint}/projects/{$xlsform->owner->odkProject->id}/forms/{$xlsform->odk_id}.svc";

        // $results = $odataClient->from('Submissions')->expand('*')->get();

        $results = Http::withToken($token)
            ->get($oDataServiceUrl . '/Submissions?$expand=*')
            ->throw()
            ->json();

        $resultsToAdd = Collect($results['value'])->whereNotIn('__id', $xlsform->submissions->pluck('id')->toArray());

        foreach($resultsToAdd as $entry) {

            $xlsformVersion = $xlsform->xlsformVersions()->firstWhere('version', $entry['__system']['formVersion']);
            $xlsformVersion?->submissions()->create([
                'id' => $entry['__id'],
                'submitted_at' => (new Carbon($entry['__system']['submissionDate']))->toDateTimeString(),
                'submitted_by' => $entry['__system']['submitterName'],
                'uuid' => $entry['__id'],
                'content' => $entry,
            ]);
        }
    }
}

