<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 3/12/2019
 * Time: 9:14 AM
 */

namespace app;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class AuthenticationTest extends TestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = new Client();
    }

    /**
     * @test
     */
    public function testAuthentication_returnsVerificationCode()
    {
        $data = array(
            'form_params' => array(
                'phoneNumber' => '+37200000766',
                'nationalIdentityNumber' => '60001019906'
            )
        );
        $url = 'http://localhost/mid-rest-php-demo/app/authentication-request';
        $response = $this->client->post($url, $data);
        $html = $response->getBody()->getContents();
        $regex = '/<h4 class="alert-heading text-center verification-code">(?<!\d)(?!0000)\d{4}(?!\d)<\/h4>/';
        $this->assertEquals(true, preg_match($regex, $html));
    }

    protected function tearDown()
    {
        $this->client = null;
    }


}
