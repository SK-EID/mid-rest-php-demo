<?php


use Sk\Middemo\Config;
use Sk\Middemo\Model\UserRequest;
use Sk\Middemo\Service\MobileIdAuthenticationService;
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
