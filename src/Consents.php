<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Illuminate\Support\Collection;

class Consents extends Collection
{
    public function getIds(): array
    {
        return $this->pluck('consent_id')->toArray();
    }

    public function getCaptions(): array
    {
        return $this->pluck('caption')->toArray();
    }
}
