<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorRequestException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorUser;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Provider;
use SocialiteProviders\Manager\Config;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_can_get_a_redirect_response()
    {
        $request = new Request;
        $session = $this->app->make('session')->driver('array');
        $request->setLaravelSession($session);

        $provider = new Provider($request, '123', 'mysecret', 'someurl');

        $redirect = $provider->redirect()->getTargetUrl();

        $this->assertStringContainsString('client_id=123', $redirect);
        $this->assertStringContainsString('redirect_uri=someurl', $redirect);
        $this->assertStringContainsString('profile.basic', $redirect);
    }

    #[Test]
    public function can_use_the_profile_extended_scope()
    {
        $request = new Request;
        $request->setLaravelSession($this->app->make('session')->driver('array'));
        $provider = new Provider($request, '123', 'mysecret', 'someurl');
        $config = new Config('123', 'mysecret', 'someurl', ['profile_extended' => true]);
        $provider->setConfig($config);

        $redirect = $provider->redirect()->getTargetUrl();

        $this->assertStringContainsString('profile.extended', $redirect);
    }

    #[Test]
    public function can_retrieve_a_user()
    {
        $request = new Request(['state' => 'state']);
        $session = $this->app->make('session')->driver('array');
        $session->put('state', 'state');
        $request->setLaravelSession($session);

        $basicProfileResponse = $this->mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns(Utils::streamFor(json_encode([
            'id' => '1',
            'email' => 'web@redsnapper.net',
            'title' => ['label' => 'Mr'],
            'firstName' => 'John',
            'lastName' => 'Doe',
            'intlPhone' => '+4412345678',
            'businessAddress' => '23 Street',
            'city' => ['label' => 'Townsville'],
            'zipCode' => 'CV123',
            'specialties' =>[
                ['code'=>'SP.WFR.CG', 'label'=>'General Surgery', 'locale'=>'en'],
            ],
            'ucis' => [
                'adelin' => 'adelin123',
                'gln'=> 'gln123',
                'lanr'=> 'lanr123',
                'npi'=>'npi123',
                'rpps'=> 'rpps123',
            ],
            'oneKeyId' => '456',
            'trustLevel' => '2',
        ])));

        $accessTokenResponse = $this->mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns(Utils::streamFor(json_encode(['access_token' => 'fake-token'])));

        $guzzle = $this->mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);

        $guzzle->expects('get')->andReturns($basicProfileResponse);

        $provider = new Provider($request, 'client_id', 'client_secret', 'redirect');

        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(HealthCareAuthenticatorUser::class, $user);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('web@redsnapper.net', $user->getEmail());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('Mr', $user->getTitle());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('+4412345678', $user->getPhoneNumber());
        $this->assertEquals('23 Street', $user->getWorkplaceAddress());
        $this->assertEquals('Townsville', $user->getCity());
        $this->assertEquals('CV123', $user->getZipCode());;
        $this->assertEquals('456', $user->getOneKeyId());
        $this->assertEquals('2', $user->getTrustLevel());
        $this->assertEquals('General Surgery',$user->getSpecialties()[0]->label);
        $this->assertEquals('SP.WFR.CG',$user->getSpecialties()[0]->code);
        $this->assertEquals('en',$user->getSpecialties()[0]->locale);
        $this->assertEquals('adelin123', $user->getProfessionalCode()->adelin());
        $this->assertEquals('gln123', $user->getProfessionalCode()->gln());
        $this->assertEquals('lanr123', $user->getProfessionalCode()->lanr());
        $this->assertEquals('npi123', $user->getProfessionalCode()->npi());
        $this->assertEquals('rpps123', $user->getProfessionalCode()->rpps());

    }


    #[Test]
    public function it_will_throw_an_exception_if_the_user_cannot_be_retrieved()
    {
        $this->expectException(HealthCareAuthenticatorRequestException::class);
        $this->expectExceptionMessage('User denied access');

        $request = new Request(['error' => 'denied', 'error_description' => 'User denied access']);
        $provider = new Provider($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }
}
