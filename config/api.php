<?php

return [
    'version' => env('API_VERSION', 'v1'),

    'legacy_routes_enabled' => filter_var(env('API_LEGACY_ROUTES_ENABLED', false), FILTER_VALIDATE_BOOL),

    'legacy_routes_sunset' => env('API_LEGACY_ROUTES_SUNSET', null),
];
