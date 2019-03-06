<?php
require_once __DIR__ . '/../../../mid-rest-php-client/ee.sk.mid/MobileIdAuthenticationHashToSign.php';
require_once __DIR__ . '/../model/AuthenticationSessionInfo.php';
require_once __DIR__ . '/../../../mid-rest-php-client/ee.sk.mid/rest/dao/request/AuthenticationRequest.php';
require_once __DIR__ . '/../../../mid-rest-php-client/ee.sk.mid/Language.php';
require_once __DIR__ . '/../exception/MidAuthException.php';

/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 1:42 PM
 */
interface MobileIdAuthenticationServiceInterface
{
    public function startAuthentication(UserRequest $userRequest): AuthenticationSessionInfo;

    public function authenticate(AuthenticationSessionInfo $authenticationSessionInfo): AuthenticationIdentity;
}

class MobileIdAuthenticationService implements MobileIdAuthenticationServiceInterface
{

    /** @var string $midAuthDisplayText */
    private $midAuthDisplayText = 'Log in with MID demo?';

    /** @var MobileIdClient $client */
    private $client;

    /**
     * MobileIdAuthenticationService constructor.
     * @param MobileIdClient $client
     */
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

    public function authenticate(AuthenticationSessionInfo $authenticationSessionInfo): AuthenticationIdentity
    {
        $userRequest = $authenticationSessionInfo->getUserRequest();
        $authenticationHash = $authenticationSessionInfo->getAuthenticationHash();
        $request = AuthenticationRequest::newBuilder()
            ->withRelyingPartyUUID($this->client->getRelyingPartyUUID())
            ->withRelyingPartyName($this->client->getRelyingPartyName())
            ->withPhoneNumber($userRequest->getPhoneNumber())
            ->withNationalIdentityNumber($userRequest->getNationalIdentityNumber())
            ->withHashToSign($authenticationHash)
            ->withLanguage(Language::ENG)
            ->withDisplayText($this->midAuthDisplayText)
            ->withDisplayTextFormat('GSM7')
            ->build();

        $authenticationResult = null;
        try {
            $response = $this->client->getMobileIdConnector()->authenticate($request);
            $sessionStatus = $this->client->getSessionStatusPoller()->fetchFinalSessionStatus(
                $response->getSessionId(),
                '/mid-api/authentication/session/{sessionId}'
            );
            $authentication = $this->client->createMobileIdAuthentication($sessionStatus, $authenticationHash);
            $validator = new AuthenticationResponseValidator();
            $authenticationResult = $validator->validate($authentication);
        } catch (Exception $e) {
            throw new MidAuthException($e);
        }
        if (!$authenticationResult->isValid()) {
            throw new MidAuthException($authenticationResult->getErrors());
        }
        return $authenticationResult->getAuthenticationIdentity();

    }
}

