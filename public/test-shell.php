<?php

echo "<pre>";

echo "whoami\n";
var_dump(shell_exec("whoami"));

echo "\nSC:\n";
var_dump(shell_exec('"C:\\Windows\\System32\\sc.exe" query BackupCenterScheduler'));

echo "\nPowerShell:\n";
var_dump(shell_exec('"C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe" -Command "Get-Service BackupCenterScheduler | Select-Object Status,Name"'));

echo "</pre>";