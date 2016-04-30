# Subscription Billing for Laravel 5

[![Build Status](https://travis-ci.org/navneetrai/laravel-subscription.svg)](https://travis-ci.org/navneetrai/laravel-subscription)
[![Coverage Status](https://coveralls.io/repos/navneetrai/laravel-subscription/badge.svg)](https://coveralls.io/r/navneetrai/laravel-subscription)
[![Total Downloads](https://poser.pugx.org/navneetrai/laravel-subscription/downloads.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![Latest Stable Version](https://poser.pugx.org/navneetrai/laravel-subscription/v/stable.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![Latest Unstable Version](https://poser.pugx.org/navneetrai/laravel-subscription/v/unstable.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![License](https://poser.pugx.org/navneetrai/laravel-subscription/license.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)

laravel-subscription is a simple laravel 5 library for creating subscription billing and handling server notifications.

---
 
- [Supported services](#supported-services)
- [Installation](#installation)
- [Registering the Package](#registering-the-package)
- [Configuration](#configuration)
- [Usage](#usage)

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
  
  'Subscription'     => Userdesk\Subscription\Facades\Subscription::class,
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
