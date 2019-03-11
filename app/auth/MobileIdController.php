<?php

use Sk\Mid\Demo\Config;
use Sk\Mid\Demo\Model\UserRequest;
use Sk\Mid\Demo\service\MobileIdAuthenticationService;
use Symfony\Component\HttpFoundation\Request;
$app->post('/authentication-request', function (Request $request) use ($app) {
    $config = new Config();
    $client = $config->mobileIdClient();
    $authenticationService = new MobileIdAuthenticationService($client);
    $userRequest = new UserRequest();
    $userRequest->setNationalIdentityNumber($request->get('nationalIdentityNumber'));
    $userRequest->setPhoneNumber($request->get('phoneNumber'));
    $authenticationSessionInfo = $authenticationService->startAuthentication($userRequest);
    $app['session']->set('authenticationSessionInfo', $authenticationSessionInfo);
    $app['session']->set('authenticationService', $authenticationService);
    return $app['twig']->render('authentication.html', ['verificationCode' => $authenticationSessionInfo->getVerificationCode()]);
});


$app->post('/authenticate', function () use ($app) {
    $authenticationService = $app['session']->get('authenticationService');
    $person = $authenticationService->authenticate($app['session']->get('authenticationSessionInfo'));
    $app['session']->clear();
    return $app['twig']->render('authenticationResult.html', ['person' => $person]);
});
