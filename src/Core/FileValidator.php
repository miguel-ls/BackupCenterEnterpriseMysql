<?php

namespace BackupCenter\Core;

use BackupCenter\Models\BackupFile;

class FileValidator
{
    public function validate(BackupFile $file): bool
    {
        if (!file_exists($file->getPath())) {
            return false;
        }

        if (!is_file($file->getPath())) {
            return false;
        }

        if ($file->getSize() <= 0) {
            return false;
        }

        return true;
    }
}