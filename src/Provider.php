<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'HCA';

    protected $scopeSeparator = ' ';

    public static function additionalConfigKeys(): array
    {
        return ['profile_extended'];
    }

    public function getScopes()
    {
        return [
            'https://auth.onekeyconnect.com/x/'.(Arr::get($this->config, 'profile_extended',
                false) ? 'profile.extended' : 'profile.basic'),
            'openid',
            'profile',
        ];
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.hcn.health/hca/authorize',
            $state);
    }

    protected function getTokenUrl()
    {
        return 'https://auth.hcn.health/hca/token';
    }

    protected function getUserByToken($token)
    {

        $account = $this->getUserInfoByToken($token, '/account');
        $profile = $this->getUserById($account['user_id']);

        $profile['email'] = $account['email'];
        $profile['signup_ucis'] = $account['uci'];

        return $profile;
    }

    protected function getUserInfoByToken(string $token, string $endpoint): array
    {
        $response = $this->getHttpClient()->get("https://api.healthcaresdks.com/api/hca/user/me/$endpoint?api-version=230505",
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

        return json_decode($response->getBody(), true);
    }

    protected function getUserById(string $id): array
    {
        try {
            return Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => config('services.hca.api_key'),
            ])
                ->retry(1, 200)
                ->get("https://apim-prod-westeu-onekey.azure-api.net/api/hca/user/b2b/user/$id/profile")
                ->throw()
                ->json();
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                throw new UserNotFoundException(
                    userId: $id,
                    responseBody: $e->response->json(),
                    previous: $e
                );
            }
            
            // Re-throw other exceptions
            throw $e;
        }
    }

    public function user()
    {
        if ($this->request->has('error')) {
            throw new HealthCareAuthenticatorRequestException($this->request->get('error_description'));
        }

        return parent::user();
    }

    protected function mapUserToObject(array $user)
    {
        return (new HealthCareAuthenticatorUser)->setRaw($user)->map([
            'id' => Arr::get($user, 'id'),
            'name' => Arr::get($user, 'firstName').' '.Arr::get($user, 'lastName'),
            'email' => Arr::get($user, 'email'),
            'title' => Arr::get($user, 'title'),
            'firstName' => Arr::get($user, 'firstName'),
            'lastName' => Arr::get($user, 'lastName'),
            'intlPhone' => Arr::get($user, 'intlPhone'),
            'workplaceAddress' => Arr::get($user, 'businessAddress'),
            'city' => Arr::get($user, 'city'),
            'zipCode' => Arr::get($user, 'zipCode'),
            'specialties' => Arr::get($user, 'specialties'),
            'professionalRegistrations' => Arr::get($user, 'professionalRegistrations'),
            'oneKeyId' => Arr::get($user, 'oneKeyId'),
            'trustLevel' => Arr::get($user, 'trustLevel'),
        ]);

    }

    protected function getTokenFields($code)
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];

        if ($this->request->has('verifier')) {
            $fields['code_verifier'] = $this->request->get('verifier');
            unset($fields['client_secret']);
        }

        return array_merge($fields, $this->parameters);
    }

    protected function hasInvalidState()
    {
        if($this->request->has('verifier')) {
            return false;
        }

        return parent::hasInvalidState();
    }
}
