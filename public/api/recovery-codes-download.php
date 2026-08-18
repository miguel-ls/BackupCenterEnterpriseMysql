<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\RecoveryCodeRepository;

require_once __DIR__.'/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method("POST");

$data=ApiController::body();

$userId=(int)($data["user_id"]??0);

if($userId<=0){

http_response_code(400);

exit;

}

$repository=new RecoveryCodeRepository(

$app->database()

);

$codes=$repository->regenerate($userId);

header("Content-Type:text/plain");

header("Content-Disposition: attachment; filename=recovery-codes.txt");

echo "Backup Center Enterprise";
echo PHP_EOL;
echo "Recovery Codes";
echo PHP_EOL;
echo "============================";
echo PHP_EOL.PHP_EOL;

foreach($codes as $code){

echo $code.PHP_EOL;

}

echo PHP_EOL;
echo "Cada código puede utilizarse una sola vez.";