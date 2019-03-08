<?php
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
$app = new Application();
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));
$config = array(
    'host_url'           => 'https://tsp.demo.sk.ee',
    'relying_party_uuid' => '00000000-0000-0000-0000-000000000000',
    'relying_party_name' => 'DEMO',
    'certificate_level'  => 'QUALIFIED',
);
$app['client.config'] = $config;
$app['debug'] = true;
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->before( function( Request $request )
{
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) )
    {
        $data = json_decode( $request->getContent(), true );
        $request->request->replace( is_array( $data ) ? $data : array() );
    }
} );
