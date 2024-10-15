<?php

return [

    // Specify which paths should have CORS applied. In this case, your API routes.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allow all HTTP methods, but it's better to specify them if you know which methods your API supports.
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    // Allow all origins, but it's more secure to specify your domains instead of '*'.
    'allowed_origins' => ['*'],

    // If needed, you can allow origins with specific patterns.
    'allowed_origins_patterns' => [],

    // Allow all headers. Specify headers explicitly if you know which ones you need.
    'allowed_headers' => ['*'],

    // No headers are exposed in this case.
    'exposed_headers' => [],

    // Cache the CORS preflight response for 1 day (86400 seconds).
    'max_age' => 86400,

    // Set to false if you don't need credentials (cookies, tokens, etc.) to be sent across domains.
    'supports_credentials' => false,
];
