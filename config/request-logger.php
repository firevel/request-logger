<?php

return [
    // Disable logger.
    'disabled' => env('REQUEST_LOGGER_DISABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    | Jobs dispatched after response are hard to debug because exception cant be
    | attached to response. It might be also not handled by some log drivers.
    |
    | You can enable debug mode to see exeptions but data stored wont be accurate.
    |
    */

    'debug' => env('REQUEST_LOGGER_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Sync job
    |--------------------------------------------------------------------------
    |
    | Job responsible for formatting and storing log.
    |
    | You can create custom job to store different fields.
    |
    */

    'job' => \Firevel\RequestLogger\LogRequest::class,

    /*
    |--------------------------------------------------------------------------
    | Log storage driver
    |--------------------------------------------------------------------------
    |
    | This option allows you to select where logs going to be stored.
    |
    | Supported: "bigquery"
    |
    */

    'driver' => env('REQUEST_LOGGER_DRIVER', 'bigquery'),

    /*
    |--------------------------------------------------------------------------
    | Log storage drivers
    |--------------------------------------------------------------------------
    |
    | Here you can define where logs are stored. Currently its only bigquery but
    | in the future it should also support stackdriver.
    |
    | Supported: "bigquery"
    |
    */
    'drivers' => [
        'bigquery' => [
            'dataset' => env('REQUEST_LOGGER_BIGQUERY_DATASET', 'requests'),
            'table' => env('REQUEST_LOGGER_BIGQUERY_TABLE', 'api'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log types enabled
    |--------------------------------------------------------------------------
    |
    | You can disabled some logs for privacy, security or performance concerns.
    |
    */

    'log' => [
        // Request parameters.
       'parameters' => true,

       // Dartabase queries.
       'queries' => true,

       // Request headers.
       'headers' => true,

       // Client IP
       'ip' => true,

       // User data
       'user' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtered keys
    |--------------------------------------------------------------------------
    |
    | This option allow to filter some data for privacy or security reasons.
    |
    | Value of selected fields will change to '[FILTERED]'.
    |
    */

    'filtered' => [
        'headers' => [
            'authorization',
            'cookie',
        ],
        'parameters' => [
            // Add parameters to be filtered for example email or password.
        ],
    ],
];
