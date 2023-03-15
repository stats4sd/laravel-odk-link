<?php

namespace Stats4sd\OdkLink\Tests;

use Stats4sd\OdkLink\Models\AppUser;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Services\OdkLinkService;

class FakeOdkLinkService extends OdkLinkService
{
    public function createProject(string $name): array
    {
        $id = OdkProject::orderByDesc('id')->first()?->id;

        return [
            'id' => ($id ?? 0) + 1,
            'name' => 'ODK Project',
            'description' => 'Description of the project',
            'archived' => false,
        ];
    }

    public function createProjectAppUser(OdkProject $odkProject): array
    {

        $id = AppUser::orderByDesc('id')->first()?->id;

        return [
            'id' => ($id ?? 0) + 1,
            'displayName' => 'App User',
            'type' => 'user',
            'token' => '12347890',
        ];
    }
}
