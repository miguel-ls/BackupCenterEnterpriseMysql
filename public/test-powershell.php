<?php

$powershell = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';

function run(string $script, string $powershell)
{
    $command = '"' . $powershell . '" -NoProfile -ExecutionPolicy Bypass -Command "' . $script . '"';

    echo "<h3>Script</h3>";
    echo "<pre>" . htmlspecialchars($script) . "</pre>";

    echo "<h3>Comando</h3>";
    echo "<pre>" . htmlspecialchars($command) . "</pre>";

    echo "<h3>Resultado</h3>";
    echo "<pre>";
    var_dump(shell_exec($command));
    echo "</pre><hr>";
}

run("(Get-CimInstance Win32_OperatingSystem).Caption", $powershell);

run("(Get-CimInstance Win32_OperatingSystem).Version", $powershell);

run("(Get-CimInstance Win32_ComputerSystem).TotalPhysicalMemory", $powershell);

run("(Get-CimInstance Win32_OperatingSystem).FreePhysicalMemory", $powershell);

run("(Get-CimInstance Win32_Processor).LoadPercentage", $powershell);

run("(Get-CimInstance Win32_OperatingSystem).LastBootUpTime", $powershell);