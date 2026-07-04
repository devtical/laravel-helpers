<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Helper Directory
    |--------------------------------------------------------------------------
    |
    | This option controls the directory where your helper files will be stored.
    | The directory is relative to the app_path().
    |
    */
    'directory' => env('HELPER_DIRECTORY', 'Helpers'),

    /*
    |--------------------------------------------------------------------------
    | Log Errors
    |--------------------------------------------------------------------------
    |
    | When enabled, helper load failures will be written to the Laravel log.
    |
    */
    'log_errors' => env('HELPER_LOG_ERRORS', true),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the application will throw an exception if a helper file
    | fails to load instead of continuing silently.
    |
    */
    'strict' => env('HELPER_STRICT', false),

];
