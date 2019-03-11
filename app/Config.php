<?php
namespace Sk\Middemo;


use Sk\Mid\MobileIdClient;
use Sk\Middemo\Model\UserMidSession;

class Config
{
    /** @var string $midRelyingPartyUuid */
    private $midRelyingPartyUuid = '00000000-0000-0000-0000-000000000000';
    
    /** @var string $midRelyingPartyName */
    private $midRelyingPartyName = 'DEMO';
    
    /** @var string $midApplicationProviderHost */
    private $midApplicationProviderHost = 'https://tsp.demo.sk.ee/mid-api';
    
    public function mobileIdClient() : MobileIdClient
    {
        return MobileIdClient::newBuilder()
            ->withRelyingPartyUUID($this->midRelyingPartyUuid)
            ->withRelyingPartyName($this->midRelyingPartyName)
            ->withHostUrl($this->midApplicationProviderHost)
            ->build();
    }

    public function userSessionSigning()
    {
        return new UserMidSession();
    }
}
