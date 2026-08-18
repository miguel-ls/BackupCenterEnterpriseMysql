<?php

namespace BackupCenter\Console;

interface Command
{
    public function execute(array $arguments = []): int;
}