<?php

namespace BackupCenter\Contracts;

interface IHostedService
{
    public function start(): void;

    public function stop(): void;
}