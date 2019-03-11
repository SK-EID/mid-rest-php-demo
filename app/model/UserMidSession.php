<?php
namespace Sk\Mid\Demo\Model;

class UserMidSession {
    /** @var SigningSessionInfo $signingSessionInfo */
    private $signingSessionInfo;
    /** @var AuthenticationSessionInfo $authenticationSessionInfo */
    private $authenticationSessionInfo;
    /**
     * @return SigningSessionInfo
     */
    public function getSigningSessionInfo(): SigningSessionInfo
    {
        return $this->signingSessionInfo;
    }
    /**
     * @param SigningSessionInfo $signingSessionInfo
     */
    public function setSigningSessionInfo(SigningSessionInfo $signingSessionInfo): void
    {
        $this->signingSessionInfo = $signingSessionInfo;
    }
    /**
     * @return AuthenticationSessionInfo
     */
    public function getAuthenticationSessionInfo(): AuthenticationSessionInfo
    {
        return $this->authenticationSessionInfo;
    }
    /**
     * @param AuthenticationSessionInfo $authenticationSessionInfo
     */
    public function setAuthenticationSessionInfo(AuthenticationSessionInfo $authenticationSessionInfo): void
    {
        $this->authenticationSessionInfo = $authenticationSessionInfo;
    }
    public function clearSigningSession() {
        $this->signingSessionInfo = null;
    }
    public function clearAuthenticationSessionInfo() {
        $this->authenticationSessionInfo = null;
    }
}

?>
