<?php
//require_once __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/app.php';
//require_once __DIR__ . '/auth/AuthenticationRequest.php';
//$app -> run();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/app.php';

require_once __DIR__ . '/auth/MobileIdController.php';
require_once __DIR__ . '/services/MobileIdAuthenticationService.php';
$app->run();
