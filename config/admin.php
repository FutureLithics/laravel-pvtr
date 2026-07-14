<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Admin User
    |--------------------------------------------------------------------------
    |
    | Credentials used by the database seeder to create (or update) the initial
    | admin account. Override these via the matching environment variables so
    | that no default password ships to a real environment.
    |
    */

    'name' => env('ADMIN_NAME', 'Admin User'),

    'email' => env('ADMIN_EMAIL', 'admin@example.com'),

    'password' => env('ADMIN_PASSWORD', 'password'),

];
