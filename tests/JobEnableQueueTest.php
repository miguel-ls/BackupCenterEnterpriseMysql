<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();
$pdo = $app->database()->getConnection();
$jobRepository = $app->jobRepository();
$queueRepository = $app->jobQueue();

$pdo->exec('DELETE FROM job_queue');
$pdo->exec('DELETE FROM jobs');

$disabledJobId = $jobRepository->createJob(
    null,
    'Trabajo deshabilitado',
    '',
    '',
    '',
    0
);

$enabledJobId = $jobRepository->createJob(
    null,
    'Trabajo habilitado',
    '',
    '',
    '',
    1
);

$disabledQueued = $queueRepository->enqueue($disabledJobId);
$enabledQueued = $queueRepository->enqueue($enabledJobId);

if ($disabledQueued) {
    throw new RuntimeException('Un trabajo deshabilitado fue encolado');
}

if (!$enabledQueued) {
    throw new RuntimeException('Un trabajo habilitado no fue encolado');
}

echo 'Job enable queue test OK' . PHP_EOL;
