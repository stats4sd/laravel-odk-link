<?php

namespace Stats4sd\OdkLink\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Models\XlsformVersion;
use Stats4sd\OdkLink\Tests\Models\User;
use Stats4sd\OdkLink\Tests\TestCase;

class XlsformTemplateTest extends TestCase
{

    use RefreshDatabase;
    public User $user;

    public function setUp(): void
    {
        parent::setup();

        $this->user = $this->setupAdminUser();
    }

    /** @test */
    public function it_can_create_xlsform_templates(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/xlsform-template')
            ->assertStatus(200);
    }



    /** @test */
    public function a_template_can_have_many_forms(): void
    {

        $xlsformTemplate = XlsformTemplate::factory()
            ->has(Xlsform::factory()->count(2))
            ->create();

        $this->assertEquals(2, $xlsformTemplate->xlsforms()->count());
    }

    /** @test */
    public function a_form_can_have_many_versions(): void
    {
        $xlsform = Xlsform::factory()
            ->has(XlsformVersion::factory()->count(2))
            ->create();

        $this->assertEquals(2, $xlsform->xlsformVersions()->count());
    }

    /** @test */
    public function a_version_can_have_many_submissions(): void
    {
        $xlsformVersion = XlsformVersion::factory()
            ->has(Submission::factory()->count(2))
            ->create();

            $this->assertEquals(2, $xlsformVersion->submissions()->count());
    }

}
