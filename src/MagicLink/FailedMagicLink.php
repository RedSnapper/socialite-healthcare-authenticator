<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink;

class FailedMagicLink
{
    public function __construct(
        public string $onekeyId,
        public string $requestEmail,
        public string $error
    ) {}
}