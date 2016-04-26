# Subscription Billing for Laravel 5

[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)
[![Total Downloads](https://poser.pugx.org/laravel/framework/downloads.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/framework/v/stable.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/framework/v/unstable.svg)](https://packagist.org/packages/laravel/framework)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

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
