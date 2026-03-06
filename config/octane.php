<?php

use App\Listeners\OctaneAppStateReset;
use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CloseMonologHandlers;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushDatabaseRecordModificationState;
use Laravel\Octane\Listeners\FlushOnce;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\FlushUploadedFiles;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Octane;
/*
--------------------------------------------------------------------------
Octane = App State Management
--------------------------------------------------------------------------
*/

return [
    # This only tells Laravel which runtime adapter is active.
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    # tls
    'https' => env('APP_SCHEME', 'http') === 'https',
    # Garbage Collection Threshold
    'garbage' => floor(env('PHP_MEMORY_LIMIT_IN_MB') * env('GARBAGE_AT_MEMORY_PERCENT')),
    # Hard safety limit for a single request / operation.
    'max_execution_time' => (int) env('REQUEST_MAX_EXECUTION_TIME', 120),
    # Max requests to reset app container to sandbox state
    'max_requests' => (int) env('MAX_REQUESTS', 1000),
    # Sleep worker for output logs
    'usleep_between_writing_server_output' => 0,
    # Event Listeners (Application Lifecycle bet requests)
    'listeners' => [
        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
            OctaneAppStateReset::class
        ],

        RequestHandled::class => [
            FlushDatabaseRecordModificationState::class,
        ],

        RequestTerminated::class => [
            FlushUploadedFiles::class,
        ],

        OperationTerminated::class => [
            FlushOnce::class,
            FlushTemporaryContainerInstances::class,
            DisconnectFromDatabases::class,
            CollectGarbage::class,
        ],

        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],

        WorkerStopping::class => [
            CloseMonologHandlers::class,
        ],

        TaskReceived::class => [
            ...Octane::prepareApplicationForNextOperation()
        ],

        TaskTerminated::class => [
            //
        ],
    ],
    # services resolved once per worker and kept in memory
    'warm' => [
        ...Octane::defaultServicesToWarm(),
    ],
    # services reset per request
    'flush' => [],
    # Database Connection Handling bet requests to avoid stale connections
    'disconnect_database_connections' => true,
];
