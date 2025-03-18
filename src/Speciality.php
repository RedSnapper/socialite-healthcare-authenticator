<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

class Speciality
{

    public function __construct(public string $code, public string $label, public string $locale)
    {
    }
}