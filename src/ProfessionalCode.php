<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Illuminate\Support\Arr;

class ProfessionalCode
{
    private array $code;

    public function __construct(array|null $code)
    {
        $this->code = is_null($code) ? [] : $code;
    }

    public function adelin():?string
    {
        return Arr::get($this->code, 'adelin');
    }

    public function gln():?string
    {
        return Arr::get($this->code, 'gln');
    }

    public function lanr():?string
    {
        return Arr::get($this->code, 'lanr');
    }

    public function npi():?string
    {
        return Arr::get($this->code, 'npi');
    }

    public function rpps():?string
    {
        return Arr::get($this->code, 'rpps');
    }

}