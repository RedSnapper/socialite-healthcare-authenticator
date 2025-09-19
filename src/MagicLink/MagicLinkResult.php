<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator\MagicLink;

class MagicLinkResult
{
    /** @var GeneratedMagicLink[] */
    public array $success;
    /** @var FailedMagicLink[] */
    public array $failed;

    public function __construct(array $success, array $failed)
    {
        $this->success = $success;
        $this->failed = $failed;
    }
}