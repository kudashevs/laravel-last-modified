<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable
    |--------------------------------------------------------------------------
    |
    | Specifies whether the LastModified middleware is enabled. When it is
    | enabled, the middleware modifies the response with the Last-Modified
    | response header and decides whether to modify the status code or not.
    | When it is disabled, it does nothing.
    |
    */

    'enable' => true,

    /*
    |--------------------------------------------------------------------------
    | Aggressive mode
    |--------------------------------------------------------------------------
    |
    | Specifies whether the LastModified middleware acts aggressively. When it
    | is, the middleware aborts any further execution and returns a response
    | with a status code. When it is not, the middleware just sets the response
    | status code and passes the response further down the chain.
    |
    */

    'aggressive' => false,

    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    |
    | Specifies the fallback time fot the Last-Modifier header. This fallback is
    | used when a route does not use any view. Theoretically, this should happen
    | rarely, but it is better to have it. The format is 'dd-mm-yyyy h:i:s'.
    |
    | Note: don't forget to update it from time to time.
    |
    */

    'fallback' => strtotime('01-01-2025 12:00:00'),

    /*
    |--------------------------------------------------------------------------
    | Last-Modified origins
    |--------------------------------------------------------------------------
    |
    | Specifies a list of origins where the middleware will look for time.
    |
    */

    'origins' => [],
];
