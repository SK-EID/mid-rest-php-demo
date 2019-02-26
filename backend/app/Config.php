<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 2/12/2019
 * Time: 5:26 PM
 */
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
    // VAATA JAVA OMA BEANE, KA YLEMISEL FNIL.
    public function userSessionSigning()
    {
        return new UserMidSession();
    }
}
