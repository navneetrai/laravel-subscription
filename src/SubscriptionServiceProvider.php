<?php namespace Userdesk\Subscription;

use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(){
		$this->app->bind('subscription', function(){
            return new Subscription;
        });
	}
	

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides(){
		return ['subscription'];
	}

}
