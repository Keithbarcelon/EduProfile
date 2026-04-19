<?php

return [
    'github' => [
        'enabled' => (bool) env('UPDATES_GITHUB_ENABLED', false),
        'latest_release_endpoint' => env('UPDATES_GITHUB_LATEST_ENDPOINT', ''),
        'release_url' => env('APP_RELEASE_GITHUB', ''),
        'verify_ssl' => (bool) env('UPDATES_GITHUB_VERIFY_SSL', true),
        'ca_bundle' => env('UPDATES_GITHUB_CA_BUNDLE', ''),
    ],
];
