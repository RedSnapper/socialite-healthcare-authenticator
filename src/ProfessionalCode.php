<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Illuminate\Support\Arr;

class ProfessionalCode
{
    /** @var array<array{name: string, value: string}> */
    private array $registrations;

    public function __construct(?array $registrations, private readonly ?string $signUpCode = null)
    {
        $this->registrations = is_null($registrations) ? [] : $registrations;
    }

    public function adeli(): ?string
    {
        return $this->getCode('ADELI');
    }

    public function gln(): ?string
    {
        return $this->getCode('GLN');
    }

    public function lanr(): ?string
    {
        return $this->getCode('LANR');
    }

    public function npi(): ?string
    {
        return $this->getCode('NPI');
    }

    public function rpps(): ?string
    {
        return $this->getCode('RPPS');
    }

    public function codiceFiscale(): ?string
    {
        return $this->getCode('CIF');
    }

    protected function getCode(string $key): ?string
    {
        return $this->findRegistrationValue($key) ?? $this->signUp();
    }

    private function findRegistrationValue(string $key): ?string
    {
        $registration = Arr::first(
            $this->registrations,
            fn($registration) => Arr::get($registration, 'name') === $key
        );

        return Arr::get($registration, 'value');
    }

    public function signUp(): ?string
    {
        return $this->signUpCode;
    }
}