<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Illuminate\Support\Arr;

class ProfessionalCode
{
    private array $code;

    public function __construct(?array $code,private ?string $signUpCode = null)
    {
        $this->code = is_null($code) ? [] : $code;
    }

    public function adelin(): ?string
    {
        return $this->getCode('adelin');
    }

    public function gln(): ?string
    {
        return $this->getCode('gln');
    }

    public function lanr(): ?string
    {
        return $this->getCode('lanr');
    }

    public function npi(): ?string
    {
        return $this->getCode('npi');
    }

    public function rpps(): ?string
    {
        return $this->getCode('rpps');
    }

    public function codiceFisacle(): ?string
    {
        return $this->getCode('cf');
    }

    protected function getCode(string $key): ?string
    {
        return Arr::get($this->code, $key,$this->signUp());
    }

    public function signUp():?string
    {
        return $this->signUpCode;
    }
}
