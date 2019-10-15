<?php

namespace Sk\Middemo\Service;

use Sk\Mid\AuthenticationIdentity;
use Sk\Mid\AuthenticationResponseValidator;
use Sk\Mid\DisplayTextFormat;
use Sk\Mid\Exception\DeliveryException;
use Sk\Mid\Exception\InvalidUserConfigurationException;
use Sk\Mid\Exception\MidInternalErrorException;
use Sk\Mid\Exception\MidSessionNotFoundException;
use Sk\Mid\Exception\MidSessionTimeoutException;
use Sk\Mid\Exception\MissingOrInvalidParameterException;
use Sk\Mid\Exception\NotMidClientException;
use Sk\Mid\Exception\PhoneNotAvailableException;
use Sk\Mid\Exception\UnauthorizedException;
use Sk\Mid\Exception\UserCancellationException;
use Sk\Mid\Language\ENG;
use Sk\Mid\MidIdentity;
use Sk\Mid\MobileIdAuthenticationHashToSign;
use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\AuthenticationRequest;
use Sk\Middemo\Exception\MidOperationException;
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
            ->withDisplayTextFormat(DisplayTextFormat::GSM7)
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
        } catch (UserCancellationException $e) {
            throw new MidOperationException("You cancelled operation from your phone.");
        } catch (NotMidClientException $e) {
            throw new MidOperationException("You are not a Mobile-ID client or your Mobile-ID certificates are revoked. Please contact your mobile operator.");
        } catch (MidSessionTimeoutException $e) {
            throw new MidOperationException("You didn't type in PIN code into your phone or there was a communication error.");
        } catch (PhoneNotAvailableException $e) {
            throw new MidOperationException("Unable to reach your phone. Please make sure your phone has mobile coverage.");
        } catch (DeliveryException $e) {
            throw new MidOperationException("Communication error. Unable to reach your phone.");
        } catch (InvalidUserConfigurationException $e) {
            throw new MidOperationException("Mobile-ID configuration on your SIM card differs from what is configured on service provider's side. Please contact your mobile operator.");
        } catch (MidSessionNotFoundException | MissingOrInvalidParameterException | UnauthorizedException $e) {
            throw new MidOperationException("Client side error with mobile-ID integration.", $e->getCode());
        } catch (MidInternalErrorException $e) {
            throw new MidOperationException("MID internal error", $e->getCode());
        }

        if (!$authenticationResult->isValid()) {
            throw new MidOperationException($authenticationResult->getErrors());
        }
        return $authenticationResult->getAuthenticationIdentity();
    }
}

