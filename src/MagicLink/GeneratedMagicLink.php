<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink;


class GeneratedMagicLink
{
    public function __construct(
        public string $onekeyId,
        public string $accountEmail,
        public string $url,
        public string $requestEmail
    ) {}
}