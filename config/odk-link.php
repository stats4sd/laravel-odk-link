<?php

// config for Stats4sd/OdkLink
return [

    'odk' => [

        /**
         * Tells the system which Aggregation system is in use. Possible values are:
         * - odk-central
         * - kobotoolbox
         */
        'aggregator' => env('ODK_SERVICE', 'odk-central'),

        /**
         * The base url for the service (without the trailing '/').
         * If you use the public Kobotoolbox, this will be
         *  - 'https://kf.kobotoolbox.org' or
         *  - 'https://kobo.humanitarianresponse.info'
         *
         * If you use a custom installation of ODK Central or Kobotoolbox, it will be the base url to your service.
         *
         *
         */
        'base_endpoint' => env('ODK_ENDPOINT', ''),

        /**
         * Username and password for the main platform account
         * The platform requires a 'primary' user account on the KoboToolbox server to manage deployments of ODK forms.
         * This account will *own* every form published by the platform.
         *
         * We recommend not using an account that individuals typically use or have access to, to avoid mismatch between forms deployed and forms in the Laravel database.
         */
        'username' => env('ODK_USERNAME', ''),
        'password' => env('ODK_PASSWORD', ''),
    ],

    'storage' => [
        'xlsforms' => config('filesystem.default'),
        'media' => config('filesystem.default'),
    ],
];
