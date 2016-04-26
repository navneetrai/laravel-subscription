<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Userdesk\Subscription\SubscriptionFactory;

class SubscriptionFactoryTest extends Orchestra\Testbench\TestCase
{   

    public function testCreateServiceThrowsExceptionIfNoConfig(){
    	$this->setExpectedException('\\Userdesk\Subscription\Exceptions\SubscriptionException');
		$factory = new SubscriptionFactory();
        $service = $factory->createService('paypal');
    }

    public function testCreateServiceNonExistentService() {
    	Config::set('subscription.services.foo.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $service = $factory->createService('foo');

        $this->assertNull($service);
    }

    public function testCreateServicePreLoaded(){
    	Config::set('subscription.services.paypal.email', 'test@best.com');

		$factory = new SubscriptionFactory();
        $service = $factory->createService('paypal');

        $this->assertInstanceOf('Userdesk\\Subscription\\Services\\Paypal', $service);
    }

    public function testRegisterServiceAlias(){
    	Config::set('subscription.services.paypal.email', 'test@best.com');
    	Config::set('subscription.services.alias.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $service = $factory->createService('alias');

        $this->assertNull($service);

        $factory->registerServiceAlias('paypal', 'alias');

        $newService = $factory->createService('alias');

        $this->assertInstanceOf('Userdesk\\Subscription\\Services\\Paypal', $newService);
    }

    public function testCreateServiceUserRegistered(){
    	Config::set('subscription.services.foo.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $service = $factory->createService('foo');

        $this->assertNull($service);

        $factory->registerService('foo', 'Userdesk\Subscription\Mocks\MockService');

        $newService = $factory->createService('foo');

        $this->assertInstanceOf('Userdesk\\Subscription\\Contracts\\Processor', $newService);
    }
}
