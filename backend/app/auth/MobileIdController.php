<?php
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ . '/../services/MobileIdAuthenticationService.php';
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../model/UserRequest.php';
require_once __DIR__ . '/../../../mid-rest-php-client-vana/ee.sk.mid/MobileIdClient.php';

//require_once __DIR__ . '/../index.php';
/**
 * Created by PhpStorm.
 * User: mikks
 * Date: 2/18/2019
 * Time: 3:46 PM
 */

$app->post('/authentication-request', function(Request $request) use($app) {
    $config = new Config();
    $client = $config->mobileIdClient();
    $authenticationService = new MobileIdAuthenticationService($client);
    $userRequest = new UserRequest();
    $userRequest->setNationalIdentityNumber($request->get('nationalIdentityNumber'));
    $userRequest->setPhoneNumber($request->get('phoneNumber'));
    $authenticationSessionInfo = $authenticationService->startAuthentication($userRequest);
    $app['session']->set('authenticationService', $authenticationService);
    $app['session']->set('authenticationSessionInfo', $authenticationSessionInfo);
    return $app['twig']->render('authentication.html', ['verificationCode' => $authenticationSessionInfo->getVerificationCode()]);
});

$app->post('/authenticate', function() use ($app) {
    $authenticationService = $app['session']->get('authenticationService');
    $person = $authenticationService->authenticate($app['session']->get('authenticationSessionInfo'));
    $app['session']->clear();
    return 'pok';
});