<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;

class OdkLinkCommand extends Command
{
    public $signature = 'laravel-odk-link';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
