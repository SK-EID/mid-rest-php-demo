<?php


namespace App\Security;


use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sk\Mid\AuthenticationResponseValidator;
use Sk\Mid\DisplayTextFormat;
use Sk\Mid\Exception\MidDeliveryException;
use Sk\Mid\Exception\MidInternalErrorException;
use Sk\Mid\Exception\MidInvalidNationalIdentityNumberException;
use Sk\Mid\Exception\MidInvalidPhoneNumberException;
use Sk\Mid\Exception\MidInvalidUserConfigurationException;
use Sk\Mid\Exception\MidNotMidClientException;
use Sk\Mid\Exception\MidPhoneNotAvailableException;
use Sk\Mid\Exception\MidServiceUnavailableException;
use Sk\Mid\Exception\MidSessionNotFoundException;
use Sk\Mid\Exception\MidSessionTimeoutException;
use Sk\Mid\Exception\MidSslException;
use Sk\Mid\Exception\MidUnauthorizedException;
use Sk\Mid\Exception\MidUserCancellationException;
use Sk\Mid\Exception\MissingOrInvalidParameterException;
use Sk\Mid\Language\ENG;
use Sk\Mid\MobileIdAuthenticationHashToSign;
use Sk\Mid\MobileIdClient;
use Sk\Mid\Rest\Dao\Request\AuthenticationRequest;
use Sk\Mid\Util\MidInputUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class MobileIdAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    private const COUNTRY_CODE_TO_NUMBER_MAP = [
        "EE" => "+372",
        "LT" => "+370"
    ];

    private MobileIdClient $midRestClient;

    private AuthenticationResponseValidator $authenticationResponseValidator;

    public function __construct(private AuthorRepository $authorRepo,
                                private EntityManagerInterface $objectManager,
                                private LoggerInterface $logger,
                                string $hostUrl,
                                string $relyingPartyUuid,
                                string $relyingPartyName,
                                string $sslPinnedPublicKeys,
                                string $trustedCaCertificatesDir)
    {
        $this->midRestClient = MobileIdClient::newBuilder()
            ->withRelyingPartyUUID($relyingPartyUuid)
            ->withRelyingPartyName($relyingPartyName)
            ->withHostUrl($hostUrl)
            ->withSslPinnedPublicKeys($sslPinnedPublicKeys)
            ->build();

        // CA certificates that issued the users' Mobile-ID certificates (test CAs for the demo environment)
        $this->authenticationResponseValidator = AuthenticationResponseValidator::newBuilder()
            ->withTrustedCaCertificatesFolder(rtrim($trustedCaCertificatesDir, '/').'/')
            ->build();
    }


    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() == '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $session = $request->getSession();

        /** @var MobileIdAuthenticationHashToSign|null $authenticationHash */
        $authenticationHash = $session->get("auth-hash");
        $nationalIdentityNumber = (string) $session->get("national-identity-number");
        $country = (string) $session->get("country");
        $phoneNumber = (string) $session->get("phone-number");

        if ($authenticationHash === null) {
            $this->fail($session, "Please start the login again.");
        }

        $phoneNumber = (self::COUNTRY_CODE_TO_NUMBER_MAP[$country] ?? '').$phoneNumber;

        try {
            $phoneNumber = MidInputUtil::getValidatedPhoneNumber($phoneNumber);
            $nationalIdentityNumber = MidInputUtil::getValidatedNationalIdentityNumber($nationalIdentityNumber);
        }
        catch (MidInvalidPhoneNumberException $e) {
            $this->fail($session, "You entered an invalid phone number '".$phoneNumber."'");
        }
        catch (MidInvalidNationalIdentityNumberException $e) {
            $this->fail($session, "You entered an invalid national identity number '".$nationalIdentityNumber."'");
        }

        //sleep so in case when user logs in from smart device, he/she can see the verification code on the webpage before the mobile id application comes up

        sleep(2);

        $authenticationRequest = AuthenticationRequest::newBuilder()
            ->withPhoneNumber($phoneNumber)
            ->withNationalIdentityNumber($nationalIdentityNumber)
            ->withHashToSign($authenticationHash)
            ->withLanguage(ENG::asType())
            ->withDisplayText("Log into mid-rest demo application?")
            ->withDisplayTextFormat(DisplayTextFormat::GSM7)
            ->build();

        try {
            $response = $this->midRestClient->getMobileIdConnector()->initAuthentication($authenticationRequest);
        }
        catch (MidNotMidClientException $e) {
            $this->fail($session, "You are not a Mobile-ID client or your Mobile-ID certificates are revoked. Please contact your mobile operator.", $e);
        }
        catch (MidUnauthorizedException $e) {
            $this->fail($session, "You are not authorized", $e);
        }
        catch (MissingOrInvalidParameterException $e) {
            $this->fail($session, "You entered missing or invalid parameters", $e);
        }
        catch (MidSslException $e) {
            $this->fail($session, "Could not verify the SSL certificate of the Mobile-ID service", $e);
        }
        catch (MidServiceUnavailableException $e) {
            $this->fail($session, "Mobile-ID service is currently unavailable. Please try again later.", $e);
        }
        catch (MidInternalErrorException $e) {
            $this->fail($session, "There was a Mid-Rest internal error", $e);
        }


        try {
            $finalSessionStatus = $this->midRestClient
                ->getSessionStatusPoller()
                ->fetchFinalSessionStatus($response->getSessionID());

            $authentication = $this->midRestClient
                ->createMobileIdAuthentication($finalSessionStatus, $authenticationHash);

            // validate the returned certificate and signature to protect against man-in-the-middle attacks
            $authenticatedPerson = $this->authenticationResponseValidator
                ->validate($authentication)
                ->getAuthenticationIdentity();

            $session->set("full_name", $authenticatedPerson->getGivenName());
        }
        catch (MidUserCancellationException $e) {
            $this->fail($session, "You cancelled operation from your phone.", $e);
        }
        catch (MidNotMidClientException $e) {
            $this->fail($session, "You are not a mid client", $e);
        }
        catch (MidSessionTimeoutException $e) {
            $this->fail($session, "You didn't type in PIN code into your phone or there was a communication error.", $e);
        }
        catch (MidPhoneNotAvailableException $e) {
            $this->fail($session, "Unable to reach your phone. Please make sure your phone has mobile coverage.", $e);
        }
        catch (MidDeliveryException $e) {
            $this->fail($session, "Communication error. Unable to reach your phone.", $e);
        }
        catch (MidInvalidUserConfigurationException $e) {
            $this->fail($session, "Mobile-ID configuration on your SIM card differs from what is configured on service provider's side. Please contact your mobile operator.", $e);
        }
        catch (MidSessionNotFoundException | MissingOrInvalidParameterException | MidUnauthorizedException | MidSslException $e) {
            $this->fail($session, "Client side error with mobile-ID integration. Error code:". $e->getCode(), $e);
        }
        catch (MidServiceUnavailableException $e) {
            $this->fail($session, "Mobile-ID service is currently unavailable. Please try again later.", $e);
        }
        catch (MidInternalErrorException $e) {
            $this->fail($session, "Something went wrong with Mobile-ID service", $e);
        }

        $session->remove("error");

        //initially there is no user, so it is just created in the database
        //In a real application a user should always be obtained from the database
        return new SelfValidatingPassport(new UserBadge(
            $authenticatedPerson->getIdentityCode(),
            fn (string $username) => $this->findOrCreateAuthor($username, $phoneNumber)
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse("/login");
    }

    private function findOrCreateAuthor(string $username, string $phoneNumber): Author
    {
        $user = $this->authorRepo->findOneBy(["username" => $username]);
        if ($user == null) {
            $user = new Author();
            $user
                ->setName($username)
                ->setTitle('Mobile ID User')
                ->setUsername($username)
                ->setCompany('Development')
                ->setShortBio('I write php')
                ->setPhone($phoneNumber);
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }
        return $user;
    }

    private function fail(SessionInterface $session, string $error, ?\Throwable $cause = null): never
    {
        // the user sees a generic message, so log the real cause for troubleshooting
        // (error level: without Monolog, Symfony's default logger writes only errors to stderr)
        if ($cause !== null) {
            $this->logger->error(sprintf('Mobile-ID authentication failed: %s: %s', $cause::class, $cause->getMessage()));
        }

        $session->set("error", $error);
        throw new CustomUserMessageAuthenticationException($error);
    }
}
