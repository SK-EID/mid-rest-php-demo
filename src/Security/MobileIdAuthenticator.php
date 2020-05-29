<?php


namespace App\Security;


use App\Controller\VerificationCodeController;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Sk\Mid\DisplayTextFormat;
use Sk\Mid\Exception\DeliveryException;
use Sk\Mid\Exception\InvalidNationalIdentityNumberException;
use Sk\Mid\Exception\InvalidPhoneNumberException;
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
use Sk\Mid\MobileIdClient;
use Sk\Mid\MobileIdClientBuilder;
use Sk\Mid\Rest\Dao\Request\AuthenticationRequest;
use Sk\Mid\Util\MidInputUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class MobileIdAuthenticator extends AbstractGuardAuthenticator
{

    private $authorRepo;

    private $objectManager;

    /**
     * @var Client
     */
    private $midRestClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(AuthorRepository $authorRepository,
                                EntityManagerInterface $objectManager,
                                LoggerInterface $logger)
    {
        $this->authorRepo = $authorRepository;
        $this->objectManager = $objectManager;
        $this->midRestClient =  MobileIdClient::newBuilder()
            ->withRelyingPartyUUID('00000000-0000-0000-0000-000000000000')
            ->withRelyingPartyName('DEMO')
            ->withHostUrl("https://tsp.demo.sk.ee/mid-api")
            ->build();
        $this->logger=$logger;
    }


    public function supports(Request $request)
    {
        if ($request->getPathInfo() != '/login' || !$request->isMethod('POST')) {
            return;
        }
        return true;
    }

    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login' || !$request->isMethod('POST')) {
            return;
        }

        $nationalIdentityNumber = $request->getSession()->get("national-identity-number");
        $authHash = $request->getSession()->get("auth-hash");
        $country = $request->getSession()->get("country");
        $phoneNumber = $request->getSession()->get("phone-number");
        $this->logger->info($nationalIdentityNumber);

        return [
            "national-identity-number" => $nationalIdentityNumber,
            "auth-hash" => $authHash,
            "session" => $request->getSession(),
            "country" => $country,
            "phone-number" => $phoneNumber
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        //initially there is no user, so it is just created in the database
        //In a real application a user should always be obtained from the database
        $username = $credentials['national-identity-number'];
        $user = $this->authorRepo->findOneBy(["username" => $username]);
        if ($user == null) {
            $author = new Author();
            $author
                ->setName($username)
                ->setTitle('Mobile ID User')
                ->setUsername($username)
                ->setCompany('Development')
                ->setShortBio('I write php')
                ->setPhone($credentials["phone-number"]);
            $this->objectManager->persist($author);
            $this->objectManager->flush();
            return $author;
        }
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {

        $countryCodeToNumberMap = [
            "EE" => "+372",
            "LT" => "+370",
            "LV" => "+371"
        ];

        $nationalIdentityNumber = $credentials["national-identity-number"];
        $authenticationHash = $credentials["auth-hash"];
        $session = $credentials["session"];
        $country = $credentials["country"];
        $phoneNumber = $credentials["phone-number"];

        $phoneNumber = $countryCodeToNumberMap[$country].$phoneNumber;

        try {
            $phoneNumber = MidInputUtil::getValidatedPhoneNumber($phoneNumber);
            $nationalIdentityNumber = MidInputUtil::getValidatedNationalIdentityNumber($nationalIdentityNumber);
        }
        catch (InvalidPhoneNumberException $e) {
            $session->set("error", "You entered an invalid phone number '".$phoneNumber."'");
        }
        catch (InvalidNationalIdentityNumberException $e) {
            $session->set("error", "You entered an invalid national identity number '".$nationalIdentityNumber."'");
        }

        //sleep so in case when user logs in from smart device, he/she can see the verification code on the webpage before the smart id application comes up

        sleep(2);

        $request = AuthenticationRequest::newBuilder()
            ->withPhoneNumber($phoneNumber)
            ->withNationalIdentityNumber($nationalIdentityNumber)
            ->withHashToSign($authenticationHash)
            ->withLanguage(ENG::asType())
            ->withDisplayText("Log into mid-rest demo application?")
            ->withDisplayTextFormat(DisplayTextFormat::GSM7)
            ->build();

        try {
            $response = $this->midRestClient->getMobileIdConnector()->initAuthentication($request);
        }
        catch (NotMidClientException $e) {
            $session->set("error", "You are not a Mobile-ID client or your Mobile-ID certificates are revoked. Please contact your mobile operator.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (UnauthorizedException $e) {
            $session->set("error", "You are not authorized");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (MissingOrInvalidParameterException $e) {
            $session->set("error", "You entered missing or invalid parameters");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (MidInternalErrorException $e) {
            $session->set("error", "There was a Mid-Rest internal error");
            throw new AuthenticationException("Mid rest login failed");
        }


        try {
            $finalSessionStatus = $this->midRestClient
                ->getSessionStatusPoller()
                ->fetchFinalSessionStatus($response->getSessionID());

            $authenticatedPerson = $this->midRestClient
                ->createMobileIdAuthentication($finalSessionStatus, $authenticationHash)
                ->getValidatedAuthenticationResult()
                ->getAuthenticationIdentity();

            $session->set("full_name" ,$authenticatedPerson->getGivenName());
        }
        catch (UserCancellationException $e) {
            $session->set("error", "You cancelled operation from your phone.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (NotMidClientException $e) {
            $session->set("error", "You are not a mid client");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (MidSessionTimeoutException $e) {
            $session->set("error", "You didn't type in PIN code into your phone or there was a communication error.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (PhoneNotAvailableException $e) {
            $session->set("error", "Unable to reach your phone. Please make sure your phone has mobile coverage.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (DeliveryException $e) {
            $session->set("error", "Communication error. Unable to reach your phone.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (InvalidUserConfigurationException $e) {
            $session->set("error", "Mobile-ID configuration on your SIM card differs from what is configured on service provider's side. Please contact your mobile operator.");
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (MidSessionNotFoundException | MissingOrInvalidParameterException | UnauthorizedException $e) {
            $session->set("error", "Client side error with mobile-ID integration. Error code:". $e->getCode());
            throw new AuthenticationException("Mid rest login failed");
        }
        catch (MidInternalErrorException $internalError) {
            $session->set("error", "Something went wrong with Mobile-ID service");
            throw new AuthenticationException("Mid rest login failed");
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse("/login");
    }
}