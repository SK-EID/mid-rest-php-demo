<?php
namespace Sk\Middemo\Model;

use Sk\Mid\MobileIdAuthenticationHashToSign;

class AuthenticationSessionInfo
{

    /** @var MobileIdAuthenticationHashToSign $authenticationHash */
    private $authenticationHash;
    /** @var string $verificationCode */
    private $verificationCode;
    /** @var UserRequest $userRequest */
    private $userRequest;
    public function __construct(Builder $builder)
    {
        $this->authenticationHash = $builder->getAuthenticationHash();
        $this->verificationCode = $builder->getVerificationCode();
        $this->userRequest = $builder->getUserRequest();
    }
    /**
     * @return MobileIdAuthenticationHashToSign
     */
    public function getAuthenticationHash(): MobileIdAuthenticationHashToSign
    {
        return $this->authenticationHash;
    }
    /**
     * @return string
     */
    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }
    /**
     * @return UserRequest
     */
    public function getUserRequest(): UserRequest
    {
        return $this->userRequest;
    }
    public static function newBuilder() : Builder
    {
        return new Builder();
    }
}
class Builder
{
    /** @var string $verificationCode */
    private $verificationCode;
    /** @var UserRequest $userRequest */
    private $userRequest;
    /** @var MobileIdAuthenticationHashToSign $authenticationHash */
    private $authenticationHash;
    /**
     * Builder constructor.
     */
    public function __construct()
    {
    }
    /**
     * @return string
     */
    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }
    /**
     * @return UserRequest
     */
    public function getUserRequest(): UserRequest
    {
        return $this->userRequest;
    }
    /**
     * @return MobileIdAuthenticationHashToSign
     */
    public function getAuthenticationHash(): MobileIdAuthenticationHashToSign
    {
        return $this->authenticationHash;
    }
    public function withAuthenticationHash(MobileIdAuthenticationHashToSign $authenticationHash): Builder
    {
        $this->authenticationHash = $authenticationHash;
        return $this;
    }
    public function withVerificationCode(string $verificationCode): Builder
    {
        $this->verificationCode = $verificationCode;
        return $this;
    }
    public function withUserRequest(UserRequest $userRequest): Builder
    {
        $this->userRequest = $userRequest;
        return $this;
    }
    public function build(): AuthenticationSessionInfo
    {
        return new AuthenticationSessionInfo($this);
    }
}


