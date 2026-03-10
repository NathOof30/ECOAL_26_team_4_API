<?php

return [
    'tokens' => [
        'name' => env('AUTH_TOKEN_NAME', 'auth_token'),
        'revoke_existing_on_login' => filter_var(env('AUTH_REVOKE_EXISTING_TOKENS_ON_LOGIN', false), FILTER_VALIDATE_BOOL),
    ],
];
