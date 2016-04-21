# Subscription Billing for Laravel 5

laravel-subscription is a simple laravel 5 library for creating subscription billing and handling server notifications.

---
 
- [Supported services](#supported-services)
- [Installation](#installation)
- [Registering the Package](#registering-the-package)
- [Configuration](#configuration)
- [Usage](#usage)
- [Basic usage](#basic-usage)
- [More usage examples](#more-usage-examples)

## Supported services

The library supports [Paypal](https://www.paypal.com) and credit card processors [2Checkout](https://www.2checkout.com/) and [CCNow](http://www.ccnow.com/). More services will be implemented soon.

Included service implementations:

 - Paypal
 - 2Checkout
 - CCNow
- more to come!


## Installation

Add laravel-subscription to your composer.json file:

```
"require": {
  "navneetrai/laravel-subscription": "dev-master"
}
```

Use composer to install this package.

```
$ composer update
```

### Registering the Package

Register the service provider within the ```providers``` array found in ```config/app.php```:

```php
'providers' => [
  // ...
  
  Userdesk\Subscription\SubscriptionServiceProvider::class,
]
```

Add an alias within the ```aliases``` array found in ```config/app.php```:


```php
'aliases' => [
  // ...
  
  'Subscription'     => Userdesk\Subscription\Facades\Userdesk\Subscription::class,
]
```

## Configuration

There are two ways to configure laravel-subscription.

#### Option 1

Create configuration file for package using artisan command

```
$ php artisan vendor:publish --provider="Userdesk\Subscription\SubscriptionServiceProvider"
```

#### Option 2

Create configuration file manually in config directory ``config/subscription.php`` and put there code from below.

```php
<?php
return [ 
  
  /*
  |--------------------------------------------------------------------------
  | Subscription Config
  |--------------------------------------------------------------------------
  */

  /**
   * Consumers
   */
  'services' => [
    'paypal'=>[
      'email'=>'', 
      'logo'=>''
    ],
  ]

];
```

### Credentials

Add your credentials to ``config/subscription.php`` (depending on which option of configuration you choose)


## Usage

### Basic usage

Just follow the steps below and you will be able to get a processor:

```php
$paypal = Subscription::processor('Paypal');
```


## Usage examples

###Paypal:

Configuration:
Add your Paypal credentials to ``config/subscription.php``

```php
'Paypal' => [
    'email'     => 'Your Paypal email',
    'logo'      => 'Your Paypal Client Secret'
],  
```
In your Controller use the following code:

```php

public function loginWithFacebook(Request $request)
{
  // get data from request
  $code = $request->get('code');
  
  // get fb service
  $fb = \OAuth::consumer('Facebook');
  
  // check if code is valid
  
  // if code is provided get user data and sign in
  if ( ! is_null($code))
  {
    // This was a callback request from facebook, get the token
    $token = $fb->requestAccessToken($code);
    
    // Send a request with it
    $result = json_decode($fb->request('/me'), true);
    
    $message = 'Your unique facebook user id is: ' . $result['id'] . ' and your name is ' . $result['name'];
    echo $message. "<br/>";
    
    //Var_dump
    //display whole array.
    dd($result);
  }
  // if not ask for permission first
  else
  {
    // get fb authorization
    $url = $fb->getAuthorizationUri();
    
    // return to facebook login url
    return redirect((string)$url);
  }
}
```
###2Checkout:

Configuration:
Add your Google credentials to ``config/subscription.php``

```php
'Google' => [
    'client_id'     => 'Your Google client ID',
    'client_secret' => 'Your Google Client Secret',
    'scope'         => ['userinfo_email', 'userinfo_profile'],
],  
```
In your Controller use the following code:

```php

public function loginWithGoogle(Request $request)
{
  // get data from request
  $code = $request->get('code');
  
  // get google service
  $googleService = \OAuth::consumer('Google');
  
  // check if code is valid
  
  // if code is provided get user data and sign in
  if ( ! is_null($code))
  {
    // This was a callback request from google, get the token
    $token = $googleService->requestAccessToken($code);
    
    // Send a request with it
    $result = json_decode($googleService->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);
    
    $message = 'Your unique Google user id is: ' . $result['id'] . ' and your name is ' . $result['name'];
    echo $message. "<br/>";
    
    //Var_dump
    //display whole array.
    dd($result);
  }
  // if not ask for permission first
  else
  {
    // get googleService authorization
    $url = $googleService->getAuthorizationUri();
    
    // return to google login url
    return redirect((string)$url);
  }
}
```


###CCNow:

Configuration:
Add your Twitter credentials to ``config/oauth-5-laravel.php``

```php
'Twitter' => [
    'client_id'     => 'Your Twitter client ID',
    'client_secret' => 'Your Twitter Client Secret',
    // No scope - oauth1 doesn't need scope
],
```
In your Controller use the following code:

```php

public function loginWithTwitter(Request $request)
{
  // get data from request
  $token  = $request->get('oauth_token');
  $verify = $request->get('oauth_verifier');
  
  // get twitter service
  $tw = \OAuth::consumer('Twitter');
  
  // check if code is valid
  
  // if code is provided get user data and sign in
  if ( ! is_null($token) && ! is_null($verify))
  {
    // This was a callback request from twitter, get the token
    $token = $tw->requestAccessToken($token, $verify);
    
    // Send a request with it
    $result = json_decode($tw->request('account/verify_credentials.json'), true);
    
    $message = 'Your unique Twitter user id is: ' . $result['id'] . ' and your name is ' . $result['name'];
    echo $message. "<br/>";
    
    //Var_dump
    //display whole array.
    dd($result);
  }
  // if not ask for permission first
  else
  {
    // get request token
    $reqToken = $tw->requestRequestToken();
    
    // get Authorization Uri sending the request token
    $url = $tw->getAuthorizationUri(['oauth_token' => $reqToken->getRequestToken()]);

    // return to twitter login url
    return redirect((string)$url);
  }
}
```

