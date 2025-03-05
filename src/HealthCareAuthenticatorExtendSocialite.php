<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use SocialiteProviders\Manager\SocialiteWasCalled;

class HealthCareAuthenticatorExtendSocialite
{
    /**
     * Register the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('hca', Provider::class);
    }
}
