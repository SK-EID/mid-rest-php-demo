<?php

use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;
$app = new Silex\Application();

$app->register( new SessionServiceProvider() );

$config = array(
    'host_url'           => 'https://tsp.demo.sk.ee',
    'relying_party_uuid' => '00000000-0000-0000-0000-000000000000',
    'relying_party_name' => 'DEMO',
    'certificate_level'  => 'QUALIFIED',
);
$app['client.config'] = $config;
//$client = new MobileIdClient();
//$client->setRelyingPartyUUID( $config['relying_party_uuid'] )
//    ->setRelyingPartyName( $config['relying_party_name'] )
//    ->setHostUrl( $config['host_url'] );
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../../views',
));

$app->before( function( Request $request )
{
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) )
    {
        $data = json_decode( $request->getContent(), true );
        $request->request->replace( is_array( $data ) ? $data : array() );
    }
} );
