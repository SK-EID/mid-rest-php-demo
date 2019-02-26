<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 5:55 PM
 */

interface MobileIdCertificateServiceInterface {
    public function getCertificate(UserRequest $userRequest) : X509Certificate;
}

class MobileIdCertificateService implements MobileIdCertificateServiceInterface
{
    /** @var MobileIdClient $client */
    private $client;

    public function getCertificate(UserRequest $userRequest): X509Certificate
    {
        $request = CertificateRequest::newBuilder()
            ->withRelyingPartyUUID($this->client->getRelyingPartyUUID())
            ->withRelyingPartyName($this->client->getRelyingPartyName())
            ->withPhoneNumber($userRequest->getPhoneNumber())
            ->withNationalIdentityNumber($userRequest->getNationalIdentityNumber())
            ->build();
        $response = $this->client->getMobileIdConnector()->getCertificate($request);
        return $this->client->createMobileIdCertificate($response);
    }
}