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
		$this->publishes([
	        __DIR__.'/resources/assets' => public_path('vendor/laravel-subscription'),
	    ], 'public');

	    $this->publishes([
	        __DIR__.'/resources/config/subscription.php' => config_path('subscription.php'),
	    ], 'config');
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
