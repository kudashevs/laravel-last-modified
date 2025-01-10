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
    | is, the middleware aborts any further execution and returns a response with
    | a status code. When it is not, the middleware just sets the response status
    | code and passes the response further down the chain.
    |
    */

    'aggressive' => false,

];
