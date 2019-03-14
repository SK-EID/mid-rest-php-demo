<?php

use Sk\Mid\Exception\InvalidNationalIdentityNumberException;
use Sk\Mid\Exception\InvalidPhoneNumberException;
use Sk\Mid\Util\MidInputUtil;
use Sk\Middemo\Config;
use Sk\Middemo\Model\UserRequest;
use Sk\Middemo\Service\MobileIdAuthenticationService;
use Symfony\Component\HttpFoundation\Request;

$app->post('/authentication-request', function (Request $request) use ($app) {


    $inputNationalIdentityNumber = $request->get('nationalIdentityNumber');
    $inputPhoneNumber = $request->get('phoneNumber');

    try {
        $phoneNumber = MidInputUtil::getValidatedPhoneNumber($inputPhoneNumber);
        $nationalIdentityNumber = MidInputUtil::getValidatedNationalIdentityNumber($inputNationalIdentityNumber);
    }
    catch (InvalidPhoneNumberException $e) {
        die('The phone number you entered is invalid');
    }
    catch (InvalidNationalIdentityNumberException $e) {
        die('The national identity number you entered is invalid');
    }

    $config = new Config();
    $client = $config->mobileIdClient();
    $authenticationService = new MobileIdAuthenticationService($client);
    $userRequest = new UserRequest();
    $userRequest->setNationalIdentityNumber($nationalIdentityNumber);
    $userRequest->setPhoneNumber($phoneNumber);
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
