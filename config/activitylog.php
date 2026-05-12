<?php

return [
    'default_log_name' => 'default',

    'default_auth_driver' => null,

    'subject_returns_soft_deleted_models' => true,

    'activity_model' => \Spatie\Activitylog\Models\Activity::class,

    'table_name' => env('ACTIVITY_LOG_TABLE', 'activity_log'),

    'database_connection' => env('ACTIVITY_LOG_DB_CONNECTION', null),

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    'delete_records_older_than_days' => 365,

    'default_log_name' => 'default',
];
