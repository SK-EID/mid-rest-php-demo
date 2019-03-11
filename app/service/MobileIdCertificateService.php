<?php
namespace Sk\Middemo\Service;

use Sk\Mid\Exception\MidInternalErrorException;
use Sk\Mid\Exception\MissingOrInvalidParameterException;
use Sk\Mid\Exception\NotMidClientException;
use Sk\Mid\Exception\UnauthorizedException;
use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\CertificateRequest;
use Sk\Middemo\Exception\MidOperationException;
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
            ->withPhoneNumber($userRequest->getPhoneNumber())
            ->withNationalIdentityNumber($userRequest->getNationalIdentityNumber())
            ->build();
        try {
            $response = $this->client->getMobileIdConnector()->getCertificate($request);
            return $this->client->createMobileIdCertificate($response);
        } catch (NotMidClientException $e) {
            throw new MidOperationException("You are not a Mobile-ID client or your Mobile-ID certificates are revoked. Please contact your mobile operator.");
        } catch (MissingOrInvalidParameterException | UnauthorizedException $e) {
            throw new MidOperationException("Client side error with mobile-ID integration.", e);
        } catch (MidInternalErrorException $e) {
            throw new MidOperationException("MID internal error", e);
        }

    }
}
