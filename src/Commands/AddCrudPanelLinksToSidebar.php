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

        $odkDropdownHtml = '<x-backpack::menu-dropdown title="ODK Forms" icon="la la-group">
    <x-backpack::menu-dropdown-item title="Xlsform Templates" icon="la la-toolbox" :link="backpack_url(\\\'xlsform-template\\\')" />
    <x-backpack::menu-dropdown-item title="Xlsforms" icon="la la-wpforms" :link="backpack_url(\\\'xlsform\\\')" />
    <x-backpack::menu-dropdown-item title="Xlsform Owners" icon="la la-key" :link="backpack_url(\\\'odk-project\\\')" />
    <x-backpack::menu-dropdown-item title=Submissions" icon="la la-clipboard-check" :link="backpack_url(\\\'submission\\\')" />
</x-backpack::menu-dropdown>';


        Artisan::call("backpack:add-menu-content '$odkDropdownHtml'");

        return self::SUCCESS;
    }
}
