<?php
namespace Sk\Middemo;


use Sk\Mid\MobileIdClient;
use Sk\Middemo\Model\UserMidSession;

class Config
{
    
    public function mobileIdClient() : MobileIdClient
    {
        return MobileIdClient::newBuilder()
            ->withRelyingPartyUUID('00000000-0000-0000-0000-000000000000')
            ->withRelyingPartyName('DEMO')
            ->withHostUrl('https://tsp.demo.sk.ee/mid-api')
            ->build();
    }

    public function userSessionSigning()
    {
        return new UserMidSession();
    }
}
