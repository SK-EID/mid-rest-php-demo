<?php
namespace Sk\Mid\Demo\Service;


use Sk\Mid\Demo\Model\UserRequest;
use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\CertificateRequest;

interface MobileIdCertificateServiceInterface {
    public function getCertificate(UserRequest $userRequest) : array;
}

class MobileIdCertificateService implements MobileIdCertificateServiceInterface
{
    /** @var MobileIdClient $client */
    private $client;

    public function getCertificate(UserRequest $userRequest): array
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
