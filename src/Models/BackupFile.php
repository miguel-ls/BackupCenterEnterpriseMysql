<?php

namespace BackupCenter\Models;

class BackupFile
{
    public function __construct(
        private string $name,
        private string $path,
        private int $size,
        private int $modified,
        private string $extension
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getModified(): int
    {
        return $this->modified;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }
}