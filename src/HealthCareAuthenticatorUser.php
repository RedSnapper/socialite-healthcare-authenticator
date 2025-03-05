<?php

namespace RedSnapper\SocialiteProviders\HealthCareAuthenticator;

use Laravel\Socialite\Two\User;

/**
 * @property-read string|null $firstName
 * @property-read string|null $lastName
 * @property-read array $title
 * @property-read string|null $intlPhone
 * @property-read string|null $workplaceAddress
 * @property-read array $city
 * @property-read string|null $zipCode
 * @property-read string|null $specialties
 * @property-read string|null $ucis
 * @property-read string|null $oneKeyId
 * @property-read string|null $trustLevel
 */
class HealthCareAuthenticatorUser extends User
{
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getTitle(): ?string
    {
        return $this->title['label'];
    }

    public function getPhoneNumber(): ?string
    {
        return $this->intlPhone;
    }

    public function getWorkplaceAddress(): ?string
    {
        return $this->workplaceAddress;
    }

    public function getCity(): ?string
    {
        return $this->city['label'];
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function getOneKeyId(): ?string
    {
        return $this->oneKeyId;
    }

    public function getSpecialtyId(): ?string
    {
        return $this->specialties;
    }

    public function getTrustLevel(): ?string
    {
        return $this->trustLevel;
    }

    public function getProfessionalCode(): ?string
    {
        return $this->ucis;
    }
}
