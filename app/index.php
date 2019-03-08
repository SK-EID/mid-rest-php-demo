<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Sk\Mid\Demo\Auth\MobileIdController as MidController;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->register( new SessionServiceProvider() );
$config = array(
    'host_url'           => 'https://tsp.demo.sk.ee',
    'relying_party_uuid' => '00000000-0000-0000-0000-000000000000',
    'relying_party_name' => 'DEMO',
    'certificate_level'  => 'QUALIFIED',
);
$app['client.config'] = $config;
$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../../views',
));
$app['debug'] = true;
$app->before( function( Request $request )
{
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) )
    {
        $data = json_decode( $request->getContent(), true );
        $request->request->replace( is_array( $data ) ? $data : array() );
    }
} );

$app->post('/authentication-request', function(Request $request) use($app) {
    MidController::authenticationRequest($request, $app);
});

$app->post('/authenticate', function() use ($app) {
    MidController::authenticate($app);
});
$app->run();
