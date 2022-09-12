<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AddCrudPanelLinksToSidebar extends Command
{
    public $signature = 'odk:crud';

    public $description = 'Adds links to the Backpack sidebar for: Xlsform Templates, Xlsforms, Xlsform Versions, Submissions';

    public function handle(): int
    {
        $xlsTemplateLinkHtml = '<li class="nav-item"><a class="nav-link" href="{{ backpack_url("xlsform-template") }}"><i class="la la-toolbox nav-icon"></i> Xlsform Templates</a></li>';

        $xlsFormLinkHtml = '<li class="nav-item"><a class="nav-link" href="{{ backpack_url("xlsform") }}"><i class="la la-wpforms nav-icon"></i> Xlsforms</a></li>';

        $odkProjectLinkHtml = '<li class="nav-item"><a class="nav-link" href="{{ backpack_url("odk-project") }}"><i class="la la-users nav-icon"></i> Xlsform Owners</a></li>';

//        $xlsFormVersionLinkHtml = '<li class="nav-item"><a class="nav-link" href="{{ backpack_url("xlsform-version") }}"><i class="la la-forms nav-icon"></i> Xlsform Versions</a></li>';

        $submissionLinkHtml = '<li class="nav-item"><a class="nav-link" href="{{ backpack_url("submission") }}"><i class="la la-clipboard-check nav-icon"></i> Submissions</a></li>';

        Artisan::call("backpack:add-sidebar-content '$xlsTemplateLinkHtml'");
        Artisan::call("backpack:add-sidebar-content '$xlsFormLinkHtml'");
        Artisan::call("backpack:add-sidebar-content '$odkProjectLinkHtml'");
//        Artisan::call("backpack:add-sidebar-content '$xlsFormVersionLinkHtml'");
        Artisan::call("backpack:add-sidebar-content '$submissionLinkHtml'");

        return self::SUCCESS;
    }
}
