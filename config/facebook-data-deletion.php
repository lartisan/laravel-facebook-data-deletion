<?php

use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;
use Lartisan\FacebookDataDeletion\Support\NullDeletionHandler;
use Lartisan\FacebookDataDeletion\Support\NullDeletionSubjectResolver;

return [
    'app_secret' => env('FACEBOOK_APP_SECRET', env('FACEBOOK_SECRET')),

    'model' => FacebookDataDeletionRequest::class,

    'resolver' => NullDeletionSubjectResolver::class,

    'deletion_handler' => NullDeletionHandler::class,

    'queue' => [
        'connection' => env('FACEBOOK_DATA_DELETION_QUEUE_CONNECTION'),
        'name' => env('FACEBOOK_DATA_DELETION_QUEUE'),
    ],

    'route' => [
        'enabled' => true,
        'prefix' => 'api/facebook',
        'name_prefix' => 'facebook-data-deletion',
        'middleware' => ['api'],
        'callback_path' => 'data-deletion',
        'status_path' => 'data-deletion/{confirmationCode}',
    ],

    'view' => 'facebook-data-deletion::status',
];
