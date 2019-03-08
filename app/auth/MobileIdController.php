<?php

namespace Sk\Mid\Demo\Auth;

use Silex\Application;
use Sk\Mid\Demo\Config;
use Sk\Mid\Demo\Model\UserRequest;
use Sk\Mid\Demo\Service\MobileIdAuthenticationService;
use Symfony\Component\HttpFoundation\Request;

class MobileIdController
{
    public static function authenticationRequest(Request $request, Application $app)
    {
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
    }

    public static function authenticate(Application $app)
    {
        $authenticationService = $app['session']->get('authenticationService');
        $person = $authenticationService->authenticate($app['session']->get('authenticationSessionInfo'));
        $app['session']->clear();
        return 'pok';
    }
}
