<?php

return [

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
		'ccnow'=>[
			'login'=>'articlevideo', 
			'key'=>'49qzy5bnywk3udpir6ywwcd4p5q52uxx',
			'url'=>env('APP_URL', 'https://www.toufee.com')
		],

		'2checkout'=>[
			'sid'=>'409877',
			'secret'=>'sanchit123'
		],
		
		'paypal'=>[
			'email'=>'paypal@toufee.com', 
			'logo'=>''
		],
	]

];
