<?php

namespace Sk\Middemo\Service;

use Exception;
use Sk\Mid\AuthenticationIdentity;
use Sk\Mid\AuthenticationResponseValidator;
use Sk\Mid\Language\ENG;
use Sk\Mid\MidIdentity;
use Sk\Mid\MobileIdAuthenticationHashToSign;
use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\AuthenticationRequest;
use Sk\Middemo\Exception\MidAuthException;
use Sk\Middemo\Model\AuthenticationSessionInfo;
use Sk\Middemo\Model\UserRequest;

interface MobileIdAuthenticationServiceInterface
{
    public function startAuthentication(UserRequest $userRequest): AuthenticationSessionInfo;

    public function authenticate(AuthenticationSessionInfo $authenticationSessionInfo): MidIdentity;
}

class MobileIdAuthenticationService implements MobileIdAuthenticationServiceInterface
{
    /** @var string $midAuthDisplayText */
    private $midAuthDisplayText = 'Log in with MID demo?';

    /** @var MobileIdClient $client */
    private $client;

    public function __construct(MobileIdClient $client)
    {
        $this->client = $client;
    }

    public function startAuthentication(UserRequest $userRequest): AuthenticationSessionInfo
    {
        $authenticationHash = MobileIdAuthenticationHashToSign::generateRandomHashOfDefaultType();
        return AuthenticationSessionInfo::newBuilder()
            ->withUserRequest($userRequest)
            ->withAuthenticationHash($authenticationHash)
            ->withVerificationCode($authenticationHash->calculateVerificationCode())
            ->build();
    }

    public function authenticate(AuthenticationSessionInfo $authenticationSessionInfo): MidIdentity
    {
        $userRequest = $authenticationSessionInfo->getUserRequest();
        $authenticationHash = $authenticationSessionInfo->getAuthenticationHash();
        $request = AuthenticationRequest::newBuilder()
            ->withPhoneNumber($userRequest->getPhoneNumber())
            ->withNationalIdentityNumber($userRequest->getNationalIdentityNumber())
            ->withHashToSign($authenticationHash)
            ->withLanguage(ENG::asType())
            ->withDisplayText($this->midAuthDisplayText)
            ->withDisplayTextFormat('GSM7')
            ->build();

        $authenticationResult = null;
        try {

            $response = $this->client->getMobileIdConnector()->initAuthentication($request);
            $sessionStatus = $this->client->getSessionStatusPoller()->fetchFinalSessionStatus(
                $response->getSessionId()
            );

            $authentication = $this->client->createMobileIdAuthentication($sessionStatus, $authenticationHash);
            $validator = new AuthenticationResponseValidator();
            $authenticationResult = $validator->validate($authentication);
        } catch (Exception $e) {
            throw new MidAuthException($e->getMessage());
        }
        if (!$authenticationResult->isValid()) {
            throw new MidAuthException($authenticationResult->getErrors());
        }
        return $authenticationResult->getAuthenticationIdentity();
    }
}

