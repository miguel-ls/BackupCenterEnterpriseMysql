<?php

namespace BackupCenter\Core;

class FileLockValidator
{
    public function isLocked(string $file): bool
    {
        $handle = @fopen($file, 'ab');

        if ($handle === false) {
            return true;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return true;
        }

        flock($handle, LOCK_UN);
        fclose($handle);

        return false;
    }
}