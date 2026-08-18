<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use DateTimeImmutable;
use DateTimeZone;

$app = new Application();
$pdo = $app->database()->getConnection();
$jobRepository = $app->jobRepository();
$queueRepository = $app->jobQueue();

$pdo->exec("DELETE FROM job_queue");
$pdo->exec("DELETE FROM jobs WHERE name LIKE 'SCHED_QUEUE_TEST_%'");

$now = new DateTimeImmutable('now', new DateTimeZone('America/Lima'));
$expression = sprintf('%d %d * * *', (int)$now->format('i'), (int)$now->format('H'));

$dueJobId = $jobRepository->createJob(
    null,
    'SCHED_QUEUE_TEST_DUE',
    '',
    '',
    $expression,
    1
);

$blankJobId = $jobRepository->createJob(
    null,
    'SCHED_QUEUE_TEST_BLANK',
    '',
    '',
    '',
    1
);

$app->schedulerEngine()->execute(false);

if (!$queueRepository->existsPendingOrRunning($dueJobId)) {
    throw new RuntimeException('El scheduler no encoló un trabajo cuyo cron coincide con la hora actual.');
}

if ($queueRepository->existsPendingOrRunning($blankJobId)) {
    throw new RuntimeException('El scheduler encoló un trabajo sin cron válido.');
}

echo 'Scheduler queue creation test OK' . PHP_EOL;

$pdo->exec("DELETE FROM job_queue");
$pdo->exec("DELETE FROM jobs WHERE id IN ($dueJobId, $blankJobId)");
