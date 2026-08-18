<?php

namespace BackupCenter\Builders;

use BackupCenter\Models\ConnectionConfig;

class ScriptBuilder
{
    private array $lines = [];

    public function batchAbort(): self
    {
        $this->lines[] = 'option batch abort';
        return $this;
    }

    public function confirmOff(): self
    {
        $this->lines[] = 'option confirm off';
        return $this;
    }

    public function open(ConnectionConfig $connection): self
    {
        $command = sprintf(
            'open sftp://%s:%s@%s:%d/ -hostkey="%s"',
            $connection->getUsername(),
            $connection->getPassword(),
            $connection->getHost(),
            $connection->getPort(),
            $connection->getHostKey()
        );

        $this->lines[] = $command;

        return $this;
    }

public function put(string $localFile, ?string $remotePath = null): self
{
    if ($remotePath === null) {
        $this->lines[] = sprintf(
            'put "%s"',
            $localFile
        );
    } else {
        $this->lines[] = sprintf(
            'put "%s" "%s"',
            $localFile,
            $remotePath
        );
    }

    return $this;
}

    public function exit(): self
    {
        $this->lines[] = 'exit';
        return $this;
    }

public function build(): string
{
    $script = implode(PHP_EOL, $this->lines);

    $this->lines = [];

    return $script;
}
}