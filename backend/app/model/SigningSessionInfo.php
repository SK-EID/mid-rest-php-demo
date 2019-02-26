<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 1:34 PM
 */

class SigningSessionInfo
{
    /** @var string $sessionId */
    private $sessionId;

    /** @var string $verificationCode */
    private $verificationCode;

    /** @var DataToSign $dataToSign  digidoc4jst tuleb */
    private $dataToSign;

    /** @var Container $container digidoc4jst */
    private $container;

    public function __construct(Builder $builder)
    {
        $this->sessionId = $builder->getSessionId();
        $this->verificationCode = $builder->getVerificationCode();
        $this->dataToSign = $builder->getDataToSign();
        $this->container = $builder->getContainer();
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

    /**
     * @return DataToSign
     */
    public function getDataToSign(): DataToSign
    {
        return $this->dataToSign;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
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

    /** @var DataToSign $dataToSign  digidoc4jst tuleb */
    private $dataToSign;

    /** @var Container $container digidoc4jst */
    private $container;

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

    /**
     * @return DataToSign
     */
    public function getDataToSign(): DataToSign
    {
        return $this->dataToSign;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
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

    public function withDataToSign(DataToSign $dataToSign): Builder
    {
        $this->dataToSign = $dataToSign;
        return $this;
    }

    public function withContainer(Container $container) : Builder
    {
        $this->container = $container;
        return $this;
    }

    public function build(): SigningSessionInfo
    {
        return new SigningSessionInfo($this);
    }


}
