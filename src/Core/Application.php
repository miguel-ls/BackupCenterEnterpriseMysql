<?php

namespace BackupCenter\Core;

use BackupCenter\Builders\ScriptBuilder;
use BackupCenter\Core\WinScpProvider;
use BackupCenter\Core\RetryPolicy;
use BackupCenter\Services\UploadManager;
use BackupCenter\Core\FileScanner;
use BackupCenter\Core\FileValidator;
use BackupCenter\Core\SystemLog;
use BackupCenter\Repositories\UploadedFileRepository;
use BackupCenter\Repositories\ExecutionHistoryRepository;

use BackupCenter\Repositories\JobRepository;
use BackupCenter\Repositories\ConnectionRepository;

use BackupCenter\Repositories\ClientRepository;

use BackupCenter\Services\BackupService;
use BackupCenter\Services\SchedulerService;

use BackupCenter\Scheduler\CronEvaluator;
use BackupCenter\Scheduler\JobRunner;
use BackupCenter\Scheduler\SchedulerEngine;
use BackupCenter\Scheduler\SchedulerLoop;

use BackupCenter\Repositories\JobQueueRepository;
use BackupCenter\Workers\BackupWorker;

use BackupCenter\Repositories\NotificationRepository;
use BackupCenter\Repositories\SystemLogRepository;
use BackupCenter\Repositories\UserRepository;
use BackupCenter\Repositories\AuditRepository;

use BackupCenter\Repositories\SettingsRepository;

class Application
{
    private ConfigurationManager $config;
    private Logger $logger;
    private ScriptBuilder $scriptBuilder;
    private RetryPolicy $retryPolicy;
    private WinScpProvider $provider;
    private UploadManager $uploadManager;
    private FileScanner $scanner;
    private FileValidator $validator;
    private UploadedFileRepository $repository;
    private ExecutionHistoryRepository $executionHistoryRepository;
    private BackupCenterAgent $agent;
    private Database $database;

private JobRepository $jobRepository;
private ConnectionRepository $connectionRepository;
private ClientRepository $clientRepository;
private BackupService $backupService;

    private SchedulerLoop $schedulerLoop;
    private SchedulerEngine $schedulerEngine;

    private JobQueueRepository $jobQueue;
    private BackupWorker $backupWorker;

    private NotificationRepository $notificationRepository;
    private SystemLogRepository $systemLogRepository;
    private UserRepository $userRepository;
    private AuditRepository $auditRepository;

private SettingsRepository $settingsRepository;

    public function __construct()
    {
        date_default_timezone_set('America/Lima');
        
        $this->database = new Database(
            Paths::database() . '/backupcenter.db'
        );

        $this->config = new ConfigurationManager(
            Paths::config() . '/config.json'
        );

        $this->logger = new Logger(
            Paths::logs()
        );

        $this->scriptBuilder = new ScriptBuilder();

        $this->retryPolicy = new RetryPolicy(
            $this->config->get('sftp.retry_attempts'),
            $this->config->get('sftp.retry_delay')
        );

        $this->provider = new WinScpProvider();

        $this->uploadManager = new UploadManager(
            $this->provider,
            $this->scriptBuilder,
            $this->retryPolicy
        );

        $this->scanner = new FileScanner();

        $this->validator = new FileValidator();

        $this->repository = new UploadedFileRepository(
            $this->database
        );

        $this->repository->initialize();
        $this->repository->initializeExecutionHistory();

        $this->executionHistoryRepository = new ExecutionHistoryRepository(
            $this->database
        );

$this->jobRepository = new JobRepository(
    $this->database
);

$this->connectionRepository = new ConnectionRepository(
    $this->database
);

$this->connectionRepository->initialize();

$this->clientRepository = new ClientRepository(
    $this->database
);

$this->clientRepository->initialize();

$this->notificationRepository = new NotificationRepository(
    $this->database
);

$this->notificationRepository->initialize();

$this->systemLogRepository = new SystemLogRepository(
    $this->database
);

$this->systemLogRepository->initialize();

$this->userRepository = new UserRepository(
    $this->database
);

$this->userRepository->initialize();

$this->auditRepository = new AuditRepository(
    $this->database
);

Audit::initialize(
    $this->auditRepository
);

SystemLog::initialize(
    $this->systemLogRepository
);

$this->settingsRepository = new SettingsRepository(
    $this->database
);

$this->settingsRepository->initialize();

$hostedAgent = new HostedAgent(
    $this,
    new Scheduler()
);

$this->backupService = new BackupService(
    $hostedAgent,
    $this->jobRepository,
    $this->connectionRepository
);

$this->jobQueue = new JobQueueRepository(
    $this->database
);

$this->jobQueue->initialize();

$this->backupWorker = new BackupWorker(
    $this->jobQueue,
    $this->backupService,
    $this->notificationRepository
);

$cron = new CronEvaluator();

$runner = new JobRunner(
    $this->backupService
);

$this->schedulerEngine = new SchedulerEngine(
    $this->jobRepository,
    $cron,
    $runner,
    $this->jobQueue
);

$this->schedulerLoop = new SchedulerLoop(
    $this->schedulerEngine
);

$this->schedulerService = new SchedulerService(
    $this->jobRepository,
    $this->backupService
);


        $this->agent = new BackupCenterAgent(
            $this->config,
            $this->logger,
            $this->scanner,
            $this->validator,
            $this->uploadManager,
            $this->repository,
            $this->executionHistoryRepository
        );
    }

    public function database(): Database
    {
        return $this->database;
    }

    public function agent(): BackupCenterAgent
    {
        return $this->agent;
    }

    public function uploadManager(): UploadManager
    {
        return $this->uploadManager;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function config(): ConfigurationManager
    {
        return $this->config;
    }

    public function executionHistoryRepository(): ExecutionHistoryRepository
    {
        return $this->executionHistoryRepository;
    }

public function backupService(): BackupService
{
    return $this->backupService;
}    

public function schedulerService(): SchedulerService
{
    return $this->schedulerService;
}

public function schedulerEngine(): SchedulerEngine
{
    return $this->schedulerEngine;
}

public function schedulerLoop(): SchedulerLoop
{
    return $this->schedulerLoop;
}

public function backupWorker(): BackupWorker
{
    return $this->backupWorker;
}

public function userRepository(): UserRepository
{
    return $this->userRepository;
}

public function auditRepository(): AuditRepository
{
    return $this->auditRepository;
}

public function jobQueue(): JobQueueRepository
{
    return $this->jobQueue;
}

public function notificationRepository(): NotificationRepository
{
    return $this->notificationRepository;
}

public function jobRepository(): JobRepository
{
    return $this->jobRepository;
}

public function connectionRepository(): ConnectionRepository
{
    return $this->connectionRepository;
}

public function clientRepository(): ClientRepository
{
    return $this->clientRepository;
}

public function settingsRepository(): SettingsRepository
{
    return $this->settingsRepository;
}

}
