<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Scout Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search engine that will be used by the
    | Laravel Scout search builder. This option may be set to any of the
    | engines listed in the "engines" configuration array below.
    |
    */

    'driver' => env('SCOUT_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Scout Engines
    |--------------------------------------------------------------------------
    |
    | Here you may control the default search engine that is used when the
    | scout service provider builds search indexes. The engines listed here
    | are supported by the package and can be used for all your needs.
    |
    */

    'engines' => [
        'algolia' => [
            'id' => env('ALGOLIA_APP_ID'),
            'secret' => env('ALGOLIA_SECRET'),
        ],
        
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST'),
            'key' => env('MEILISEARCH_KEY'),
        ],
        
        'database' => [
            'prefix' => env('SCOUT_DATABASE_PREFIX', 'scout_'),
            'soft_delete' => env('SCOUT_DATABASE_SOFT_DELETE', false),
        ],
        
        'collection' => [
            'name' => env('SCOUT_COLLECTION_NAME', 'scout_collection'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | The queue option for Scout allows you to control if the operations that
    | sync your searchable models with the search indexes are queued. When
    | this option is set to "true" all sync operations will be queued.
    |
    */

    'queue' => env('SCOUT_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | The connection name for the queue that Scout jobs will be dispatched to
    | when the queue option is set to "true". This should match a connection
    | name defined in the "queue" configuration file.
    |
    */

    'queue_connection' => env('SCOUT_QUEUE_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    |
    | This value represents the number of models that will be imported into
    | the search index at a time. This value is especially important for
    | large datasets and allows you to control the memory usage.
    |
    */

    'chunk' => [
        'searchable' => env('SCOUT_CHUNK_SEARCHABLE', 500),
        'unsearchable' => env('SCOUT_CHUNK_UNSEARCHABLE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to include soft deleted records
    | in the search results. This is useful when you want to search through
    | all records including those that have been soft deleted.
    |
    */

    'soft_delete' => env('SCOUT_SOFT_DELETE', false),

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to add the user ID to the
    | searchable record. This is useful when you want to search through
    | records that belong to a specific user.
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Before Search
    |--------------------------------------------------------------------------
    |
    | This callback will be called before the search query is executed. You
    | can use this to modify the query or add additional constraints.
    |
    */

    'before_search' => null,

    /*
    |--------------------------------------------------------------------------
    | After Search
    |--------------------------------------------------------------------------
    |
    | This callback will be called after the search query is executed. You
    | can use this to modify the results or add additional data.
    |
    */

    'after_search' => null,

];
