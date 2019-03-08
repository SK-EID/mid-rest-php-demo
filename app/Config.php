<?php

use Sk\Mid\MobileIdClient;

class Config
{
    /** @var string $midRelyingPartyUuid */
    private $midRelyingPartyUuid = '00000000-0000-0000-0000-000000000000';

    /** @var string $midRelyingPartyName */
    private $midRelyingPartyName = 'DEMO';

    /** @var string $midApplicationProviderHost */
    private $midApplicationProviderHost = 'https://tsp.demo.sk.ee';

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
