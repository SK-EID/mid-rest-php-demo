<?php
namespace Sk\Middemo\Model;

class UserMidSession {

    private $authenticationSessionInfo;

    public function getAuthenticationSessionInfo(): AuthenticationSessionInfo
    {
        return $this->authenticationSessionInfo;
    }


    public function setAuthenticationSessionInfo(AuthenticationSessionInfo $authenticationSessionInfo): void
    {
        $this->authenticationSessionInfo = $authenticationSessionInfo;
    }

    public function clearAuthenticationSessionInfo() {
        $this->authenticationSessionInfo = null;
    }
}
