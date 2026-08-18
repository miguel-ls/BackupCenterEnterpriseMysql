<?php

namespace BackupCenter\Core;

class ServiceContainer
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application();
    }

    public function app(): Application
    {
        return $this->application;
    }
}