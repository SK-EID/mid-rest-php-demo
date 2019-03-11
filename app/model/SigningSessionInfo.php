<?php
namespace Sk\Middemo\Model;

class SigningSessionInfo
{
    /** @var string $sessionId */
    private $sessionId;
    /** @var string $verificationCode */
    private $verificationCode;

    public function __construct(SigningSessionInfoBuilder $builder)
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


    public static function newBuilder() : SigningSessionInfoBuilder
    {
        return new SigningSessionInfoBuilder();
    }
}
class SigningSessionInfoBuilder
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

    public function withSessionId(string $sessionId) : SigningSessionInfoBuilder
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function withVerificationCode(string $verificationCode) : SigningSessionInfoBuilder
    {
        $this->verificationCode = $verificationCode;
        return $this;
    }

    public function build(): SigningSessionInfo
    {
        return new SigningSessionInfo($this);
    }
}
