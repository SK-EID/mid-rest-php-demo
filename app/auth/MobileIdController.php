<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../model/UserRequest.php';
use Symfony\Component\HttpFoundation\Request;

$app->post('/authentication-request', function (Request $request) use ($app) {

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

$app->post('/authenticate', function () use ($app) {
    $authenticationService = $app['session']->get('authenticationService');
    $person = $authenticationService->authenticate($app['session']->get('authenticationSessionInfo'));
    $app['session']->clear();
    return 'pok';
});


