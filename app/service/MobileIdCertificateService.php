<?php
namespace Sk\Middemo\Service;

use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\CertificateRequest;
use Sk\Middemo\Model\UserRequest;

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
        $response = $this->client->getMobileIdConnector()->pullCertificate($request);
        return $this->client->createMobileIdCertificate($response);
    }
}
