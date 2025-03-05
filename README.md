# Healthcare Authenticator

Allows for web portals for HCPs to seamlessly implement and maintain sign-on, authentication, and/or verification through integration with one of the world’s largest and most accurate sources of HCP data.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rs/socialite-healthcare-authenticator.svg?style=flat-square)](https://packagist.org/packages/rs/socialite-doccheck)
[![GitHub Tests Action Status](https://github.com/redsnapper/socialite-healthcare-authenticator/workflows/run-tests/badge.svg)](https://github.com/redsnapper/socialite-doccheck/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/rs/socialite-healthcare-authenticator.svg?style=flat-square)](https://packagist.org/packages/rs/socialite-healthcare-authenticator)

## Installation

You can install the package via composer:

```bash
composer require rs/socialite-healthcare-authenticator
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'hca' => [    
  'client_id' => env('HCA_CLIENT_ID'),  
  'client_secret' => env('HCA_CLIENT_SECRET'),  
  'redirect' => env('HCA_REDIRECT_URI'),
  'profile_extended'=>true // Set this to false if you dont have access to the full profile
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorExtendSocialite::class
    ],
];
```

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('hca')->redirect();
```

Available methods for the returned user.

```php
$user  = Socialite::driver('hca')->user();

$user->getId();
$user->getEmail();
$user->getName();
$user->getTitle();
$user->getFirstName();
$user->getLastName();
$user->getPhoneNumber();
$user->getWorkplaceAddress();
$user->getCity();
$user->getZipCode();
$user->getSpecialtyId();
$user->getProfessionalCode();
$user->getOneKeyId();
$user->getTrustLevel();

```

### Handling errors

When calling the `user` method, there are two exceptions that may be thrown.

`\RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorRequestException`

This exception is thrown if the user cancels the sign-up process or fails to verify as an HCP.

`\Laravel\Socialite\Two\InvalidStateException`

This exception is thrown if the state returned by the HCA service does not match the state stored in the session.

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email param@redsnapper.net instead of using the issue tracker.

## Credits

-   [Param Dhaliwal](https://github.com/rs)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


