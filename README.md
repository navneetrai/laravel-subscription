# Subscription Billing for Laravel 5

[![Build Status](https://travis-ci.org/navneetrai/laravel-subscription.svg)](https://travis-ci.org/navneetrai/laravel-subscription)
[![Coverage Status](https://coveralls.io/repos/navneetrai/laravel-subscription/badge.svg)](https://coveralls.io/r/navneetrai/laravel-subscription)
[![Total Downloads](https://poser.pugx.org/navneetrai/laravel-subscription/downloads.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![Latest Stable Version](https://poser.pugx.org/navneetrai/laravel-subscription/v/stable.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![Latest Unstable Version](https://poser.pugx.org/navneetrai/laravel-subscription/v/unstable.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)
[![License](https://poser.pugx.org/navneetrai/laravel-subscription/license.svg)](https://packagist.org/packages/navneetrai/laravel-subscription)

laravel-subscription is a simple laravel 5 library for creating subscription billing and handling server notifications. It is primarily meant for people outside countries like US, UK and Canada where [Stripe](https://stripe.com/), [Paypal Payments Pro](https://www.paypal.com/webapps/mpp/paypal-payments-pro) are not available.

If you want to handle non-recurring payments, you can use [Omnipay](http://omnipay.thephpleague.com/) for one-time payments and token billing.

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
  "navneetrai/laravel-subscription": "^1.0"
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

  /*
  |--------------------------------------------------------------------------
  | Subscription Services
  |--------------------------------------------------------------------------
  |
  | This file is for storing the credentials for subscription services such
  | as Paypal, CCNow, 2Checkout, and others. This file provides a sane
  | default location for this type of information, allowing packages
  | to have a conventional place to find your various credentials.
  |
  */
  'services' => [
    'paypal'=>[
      'email'=>'', 
      'logo'=>'',
      'auth'=>''
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

#### Getting Processor Informationation

You can get basic Information for any processor by:

```php
$processor = Subscription::processor($proc);

$info = $processor->info();
```

The value returned is a ``ProcessorInfo`` object. You can call ``getName``, ``getLogo`` and ``getUrl`` methods on this processor to display Processor Name, Logo and Website Url for display purposes.

For ``getLogo`` method to work correctly you'll need to copy package assets to your project using

```
$ php artisan vendor:publish --provider="Userdesk\Subscription\SubscriptionServiceProvider"
```

#### Completing Subscription

Once you have the processor object you can call:

```php
$processor = Subscription::processor($proc);

$processor->complete($id, $product, $consumer);
```

Complete method redirects to source processor so that your user can complete his payment.

``$id`` is your unique Order ID. ``$product`` and ``$consumer`` are objects implementing ``SubscriptionProductContract`` and ``SubscriptionConsumerContract`` respectively.

A basic implementation of ``SubscriptionProductContract`` and ``SubscriptionConsumerContract`` are included with source in form of ``Classes\SubscriptionProduct`` and ``Classes\SubscriptionConsumer`` respectively.

#### Handling Processor Notifications

You can handle Processor Notifications and Processor Cart Return Data by forwarding them to ``ipn`` and ``pdt`` functions respectively. 

Both these function excpects only one input with request data as array and returns ``TransactionResult`` object.

```php
public function cartComplete(Request $request, $proc){
	$processor = Subscription::processor($proc);
	try{
		$result = $processor->pdt($request->all());
	}catch(TransactionException $exception){
		Log::error($exception->getMessage());	
	}
	
	if(!empty($result)){
		$cartId = $result->getId();
	  	if(!empty($cartId)){
	  		$action = $result->getAction();    
			if($action=='signup'){
				//Handle successful Signup
			}
		}else{
			Log::error('Cart Not Found For PDT', ['proc'=>$proc, 'data'=>$request->all()]);	
		}
	}
}
```

```php
public function handleIpn(Request $request, $proc){
	$processor = Subscription::processor($proc);
	try{
	  	$result = $processor->ipn($request->all());
	}catch(Userdesk\Subscription\Exceptions\TransactionException $exception){
	  	//Handle Exceptions
	  	Log::error($exception->getMessage());  
	}

	if(!empty($result)){
	  	$cartId = $result->getId();
	  	if(!empty($cartId)){
		    $action = $result->getAction();        

		    if($action=='signup'){
		      //Handle Signup Code
		    }else if($action=='payment'){          
		      $transactionId = $result->getIdent();
		      $amount = $result->getAmount();
		      //Handle successful payments
		    }else if($action=='refund'){          
		      $transactionId = $result->getIdent();
		      $amount = $result->getAmount();
		      //Handle refunds
		    }else if($action=='cancel'){
		      //Handle cancellations;
		    }
		}else{
		    Log::error('Cart Not Found For IPN', ['proc'=>$proc, 'data'=>$request->all()]); 
		}
	}   
}
```

**It is important to remember that IPN notifications are generally delivered via POST. So, you should add post method and remove csrf check for any route handling ipn notifications.**