<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\Tests\MagicLink;


use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkClientException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkConnectionException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkServerException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink\GeneratedMagicLink;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink\MagicLink;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Tests\TestCase;

class MagicLinkTest extends TestCase
{
    protected string $clientId = '1234';
    protected string $apiKey = 'apikey';
    protected string $redirectUrl = 'https://example.com/callback';

    public function test_successful_magic_links()
    {
        $recipients = [
            ['onekey_id' => 'ABC123', 'email' => 'user@example.com', 'locale' => 'en-US']
        ];

        $responseData = [
            'links' => [
                [
                    'onekey_id' => 'ABC123',
                    'request_email' => 'user@example.com',
                    'account_email'=>'account@example.com',
                    'url' => 'https://magiclink.test/abc123',
                    'expiry' => now()->addHour()->toIso8601String()
                ]
            ],
            'failed_links'=>[]
        ];

        Http::fake([
            '/b2b' => Http::response($responseData)
        ]);

        $magicLink = new MagicLink($this->clientId, $this->apiKey, '/callback');
        $result = $magicLink->createLinks($recipients,30);


        Http::assertSent(function (Request $request) {

            $this->assertEquals([$this->apiKey],$request->header('Ocp-Apim-Subscription-Key'));
            $this->assertEquals($this->clientId,$request->data()['links']['client_id']);
            $this->assertEquals('http://localhost/callback',$request->data()['links']['redirect_uri']);
            $this->assertEquals(30*60,$request->data()['links']['duration_in_second']);;

           return true;

        });

        $this->assertCount(1, $result->success);
        $this->assertCount(0, $result->failed);

        $link = $result->success[0];
        $this->assertInstanceOf(GeneratedMagicLink::class, $link);
        $this->assertEquals('ABC123', $link->onekeyId);
        $this->assertEquals('user@example.com', $link->requestEmail);
        $this->assertEquals('account@example.com', $link->accountEmail);
        $this->assertEquals('https://magiclink.test/abc123', $link->url);

    }

    public function test_partial_failure()
    {
        $recipients = [
            ['onekey_id' => 'ABC123', 'email' => 'user1@example.com', 'locale' => 'en-US'],
            ['onekey_id' => 'DEF456', 'email' => 'user2@example.com', 'locale' => 'en-US']
        ];

        $responseData = [
            'links'=>[],
            'failed_links' => [
                [
                    'onekey_id' => 'DEF456',
                    'request_email' => 'user2@example.com',
                    'error' => 'Invalid email'
                ]
            ]
        ];

        Http::fake([
            '/b2b' => Http::response($responseData, 200)
        ]);

        $magicLink = new MagicLink($this->clientId, $this->apiKey, $this->redirectUrl);
        $result = $magicLink->createLinks($recipients);

        $this->assertCount(0, $result->success);
        $this->assertCount(1, $result->failed);

        $this->assertEquals('DEF456', $result->failed[0]->onekeyId);
        $this->assertEquals('user2@example.com', $result->failed[0]->requestEmail);
        $this->assertEquals('Invalid email', $result->failed[0]->error);
    }

    public function test_client_error_throws_exception()
    {
        Http::fake([
            '/b2b' => Http::response([], 400)
        ]);

        $magicLink = new MagicLink($this->clientId, $this->apiKey, $this->redirectUrl);

        $this->expectException(MagicLinkClientException::class);

        $magicLink->createLinks([['onekey_id' => 'ABC123', 'email' => 'user@example.com']]);
    }

    public function test_server_error_throws_exception()
    {
        Http::fake([
            '/b2b' => Http::response([], 500)
        ]);

        $magicLink = new MagicLink($this->clientId, $this->apiKey, $this->redirectUrl);

        $this->expectException(MagicLinkServerException::class);

        $magicLink->createLinks([['onekey_id' => 'ABC123', 'email' => 'user@example.com']]);
    }

    public function test_connection_error_throws_exception()
    {
        Http::fake([
            '/b2b' => Http::failedConnection()
        ]);

        $magicLink = new MagicLink($this->clientId, $this->apiKey, $this->redirectUrl);

        $this->expectException(MagicLinkConnectionException::class);

        $magicLink->createLinks([['onekey_id' => 'ABC123', 'email' => 'user@example.com']]);
    }

    public function test_constructor_uses_config_defaults_in_request()
    {
        // Arrange: set config values
        Config::set('services.hca.client_id', 'config-client-id');
        Config::set('services.hca.api_key', 'config-api-key');
        Config::set('services.hca.redirect', '/callback');

        Http::fake([
            '/b2b' => Http::response(['links' => [], 'failed_links' => []], 200)
        ]);

        $magicLink = new MagicLink(); // no params, should fall back to config
        $magicLink->createLinks([]);

        // Assert that the request used the config values
        Http::assertSent(function ($request) {
            return $request->data()['links']['client_id'] === 'config-client-id'
                && $request->header('Ocp-Apim-Subscription-Key')[0] === 'config-api-key'
                && $request->data()['links']['redirect_uri'] === url('/callback');
        });
    }

}