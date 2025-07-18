# Healthcare Authenticator

Allows for web portals for HCPs to seamlessly implement and maintain sign-on, authentication, and/or verification through integration with one of the world’s largest and most accurate sources of HCP data.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rs/socialite-healthcare-authenticator.svg?style=flat-square)](https://packagist.org/packages/rs/socialite-healthcare-authenticator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/redsnapper/socialite-healthcare-authenticator/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/redsnapper/socialite-healthcare-authenticator/actions?query=workflow%3Atests+branch%3Amain)
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
  'api_key'=>env('HCA_API_KEY')
],
```
Add the API key for user consents and magic links.

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

Can provide the locale using the with method.

```php
return Socialite::driver('hca')->with(['locale'=>'it-IT'])->redirect();
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
$user->getSpecialties(); // [Speciality]
$user->getProfessionalCode(); // ProfessionalCode
$user->getOneKeyId();
$user->getTrustLevel();

```
### Professional Code

You can also retrieve the user's professional code using the `getProfessionalCode` method.
This returns back a ProfessionalCode object. The professional code has a method for each professional code type. 

````php

$professionalCode = $user->getProfessionalCode();
$professionalCode->codeFiscale();
````

The code is sourced from Onekey data. If unavailable, it defaults to the code provided during the signup process.

### Consents

You can also retrieve the user's consents using the `consents` method.

```php
$user->consents()->all();
```

This returns back a Laravel collection.

```php
$user->consents()->ids(); // [1,2,3]
$user->consents()->captions(); // ['Consent 1','Consent 2']
```

### Handling errors

When calling the `user` method, the following exceptions may be thrown:

`\RedSnapper\SocialiteProviders\HealthCareAuthenticator\UserNotFoundException`

This exception is thrown when a user is not found in the Healthcare Authenticator system (404 response). It provides access to the user ID and response body.

`\RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorRequestException`

This exception is thrown if the user cancels the sign-up process or fails to verify as an HCP.

`\Laravel\Socialite\Two\InvalidStateException`

This exception is thrown if the state returned by the HCA service does not match the state stored in the session.

`\Illuminate\Http\Client\RequestException`

This exception is thrown for other HTTP errors (500, 503, etc.).

#### Example exception handling:

```php
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\UserNotFoundException;
use RedSnapper\SocialiteProviders\HealthCareAuthenticator\HealthCareAuthenticatorRequestException;
use Illuminate\Http\Client\RequestException;

try {
    $user = Socialite::driver('hca')->user();
} catch (UserNotFoundException $e) {
    // Handle user not found
    Log::warning('User not found in HCA', [
        'user_id' => $e->getUserId(),
        'response' => $e->getResponseBody(),
    ]);
    return redirect()->route('login')
        ->with('error', 'Account not found in Healthcare Authenticator.');
} catch (HealthCareAuthenticatorRequestException $e) {
    // Handle user cancellation or verification failure
    return redirect()->route('login')
        ->with('error', 'Authentication failed: ' . $e->getMessage());
} catch (InvalidStateException $e) {
    // Handle state mismatch
    return redirect()->route('login')
        ->with('error', 'Authentication state mismatch. Please try again.');
} catch (RequestException $e) {
    // Handle other HTTP errors
    Log::error('HCA request failed', [
        'status' => $e->response->status(),
        'message' => $e->getMessage(),
    ]);
    return redirect()->route('login')
        ->with('error', 'An error occurred during authentication.');
}
```

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


