<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Model
    |--------------------------------------------------------------------------
    |
    | When auditing, the system needs to know which Eloquent model to use.
    | The model provided here should extend the OwenIt\Auditing\Models\Audit model.
    |
    */
    'model' => \OwenIt\Auditing\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | When auditing, the system needs to know which Eloquent model should be used
    | to retrieve the users from the database.
    |
    */
    'user' => [
        'model' => \App\Models\User::class,
        'resolver' => \OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Resolvers
    |--------------------------------------------------------------------------
    |
    | The resolvers are used to retrieve additional information from the
    | request or environment that should be stored alongside the audit data.
    |
    */
    'resolvers' => [
        'ip_address' => \OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => \OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => \OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Events
    |--------------------------------------------------------------------------
    |
    | The Eloquent events that trigger an audit.
    |
    */
    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Enable strict mode to only audit models that have the
    | OwenIt\Auditing\Contracts\Auditable interface implemented.
    |
    */
    'strict' => false,

    /*
    |--------------------------------------------------------------------------
    | Empty Values
    |--------------------------------------------------------------------------
    |
    | When an attribute value is empty, it can be excluded from the audit.
    |
    */
    'empty_values' => [
        'string' => '',
        'array'  => [],
        'object' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Timestamps
    |--------------------------------------------------------------------------
    |
    | If the audit timestamps are enabled, the package will automatically
    | set the created_at and updated_at fields on the audit model.
    |
    */
    'timestamps' => true,

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    |
    | The default audit driver used to keep track of changes.
    |
    */
    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Audit Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the different audit drivers.
    |
    */
    'drivers' => [
        'database' => [
            'table'      => 'audits',
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Console
    |--------------------------------------------------------------------------
    |
    | Whether to audit artisan commands that interact with models.
    |
    */
    'console' => false,

    /*
    |--------------------------------------------------------------------------
    | Audit Queue
    |--------------------------------------------------------------------------
    |
    | Whether to queue the audit jobs for better performance.
    |
    */
    'queue' => [
        'enabled' => true,
        'connection' => null,
        'queue' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Threshold
    |--------------------------------------------------------------------------
    |
    | The maximum number of audit records that can be stored.
    |
    */
    'threshold' => 10000,

    /*
    |--------------------------------------------------------------------------
    | Audit Exclusions
    |--------------------------------------------------------------------------
    |
    | The attributes that should be excluded from the audit.
    |
    */
    'exclusions' => [
        'password',
        'password_confirmation',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verified_at',
        'current_password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Redactions
    |--------------------------------------------------------------------------
    |
    | The attributes that should be redacted in the audit.
    |
    */
    'redactions' => [
        'email',
        'phone',
        'credit_card',
        'ssn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Implementations
    |--------------------------------------------------------------------------
    |
    | The models that should be audited.
    |
    */
    'implementations' => [
        \App\Models\User::class,
        \App\Models\Book::class,
        \App\Models\Order::class,
        \App\Models\Category::class,
        \App\Models\Review::class,
    ],
];
