<?php
namespace Sk\Mid\Demo\Model;

class SigningSessionInfo
{
    /** @var string $sessionId */
    private $sessionId;
    /** @var string $verificationCode */
    private $verificationCode;

    public function __construct(Builder $builder)
    {
        $this->sessionId = $builder->getSessionId();
        $this->verificationCode = $builder->getVerificationCode();
    }
    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
    /**
     * @return string
     */
    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }


    public static function newBuilder() : Builder
    {
        return new Builder();
    }
}
class Builder
{
    /** @var string $sessionId */
    private $sessionId;
    /** @var string $verificationCode */
    private $verificationCode;

    /**
     * Builder constructor.
     */
    public function __construct()
    {
    }
    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
    /**
     * @return string
     */
    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }

    public function withSessionId(string $sessionId) : Builder
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function withVerificationCode(string $verificationCode) : Builder
    {
        $this->verificationCode = $verificationCode;
        return $this;
    }

    public function build(): SigningSessionInfo
    {
        return new SigningSessionInfo($this);
    }
}
