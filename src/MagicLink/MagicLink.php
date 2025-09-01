<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkClientException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkConnectionException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\Exceptions\MagicLinkServerException;

class MagicLink
{

    private string|null $clientId;

    private string|null $apiKey;

    private string|null $redirectUrl;

    public function __construct(?string $clientId = null, ?string $apiKey = null,?string $redirectUrl = null)
    {
        $this->clientId = $clientId ?? config('services.hca.client_id');
        $this->apiKey = $apiKey ?? config('services.hca.api_key');
        $this->redirectUrl = $redirectUrl ?? config('services.hca.redirect');
    }

    public function createLinks(array $recipients, int $durationMinutes = 60): MagicLinkResult
    {

        $durationSeconds = $durationMinutes * 60;
        $redirect = $this->formatRedirectUrl($this->redirectUrl);

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
            ])->post('https://apim-prod-westeu-onekey.azure-api.net/api/hca/link/b2b/', [
                'links' => [
                    'recipients'         => $recipients,
                    'client_id'          => $this->clientId,
                    'redirect_uri'       => $redirect,
                    'scope'              => 'openid https://auth.onekeyconnect.com/x/profile.extended',
                    'response_mode'      => 'query',
                    'response_type'      => 'code',
                    'duration_in_second' => $durationSeconds,
                ],
            ]);

            $response->throw();

            // Map API results to MagicLinkResult
            return $this->mapResponseToResult($response->json());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new MagicLinkConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response?->status() ?? 0;

            if ($status >= 400 && $status < 500) {
                throw new MagicLinkClientException($e->getMessage(), $status, $e);
            } elseif ($status >= 500) {
                throw new MagicLinkServerException($e->getMessage(), $status, $e);
            } else {
                throw new MagicLinkException($e->getMessage(), $status, $e);
            }
        }

    }

    protected function mapResponseToResult(array $apiResults): MagicLinkResult
    {

        $success = collect($apiResults['links'])->map(function ($item) {
            return new GeneratedMagicLink(
                onekeyId: $item['onekey_id'],
                accountEmail: $item['account_email'],
                url: $item['url'],
                requestEmail: $item['request_email']
            );
        });

        $failed = collect($apiResults['failed_links'])->map(function ($item) {
            return new FailedMagicLink(
                onekeyId: $item['onekey_id'],
                requestEmail: $item['request_email'],
                error: $item['error']
            );
        });


        return new MagicLinkResult($success->all(), $failed->all());
    }


    /**
     * Normalize relative redirect URLs to absolute URLs.
     */
    protected function formatRedirectUrl(string $redirect): string
    {
        return Str::startsWith($redirect, '/')
            ? app('url')->to($redirect)
            : $redirect;
    }

}