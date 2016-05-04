<?php namespace Userdesk\Tests\Subscription;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Userdesk\Subscription\SubscriptionFactory;

use Config;

class SubscriptionFactoryTest extends \Orchestra\Testbench\TestCase
{   

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::createService
     */
    public function testCreateServiceThrowsExceptionIfNoConfig(){
    	$this->setExpectedException('\\Userdesk\Subscription\Exceptions\SubscriptionException');
		$factory = new SubscriptionFactory();
        $service = $factory->createService('paypal');
    }

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::createService
     * @covers Userdesk\Subscription\SubscriptionFactory::fullyQualifiedServiceName
     * @covers Userdesk\Subscription\SubscriptionFactory::buildService
     */
    public function testCreateServiceNonExistentService() {
    	Config::set('subscription.services.foo.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $service = $factory->createService('foo');

        $this->assertNull($service);
    }

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::createService
     * @covers Userdesk\Subscription\SubscriptionFactory::fullyQualifiedServiceName
     * @covers Userdesk\Subscription\SubscriptionFactory::buildService
     */
    public function testCreateServicePreLoaded(){
    	Config::set('subscription.services.paypal.email', 'test@best.com');

		$factory = new SubscriptionFactory();
        $service = $factory->createService('paypal');

        $this->assertInstanceOf('Userdesk\\Subscription\\Services\\Paypal', $service);
    }
	
	/**
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     */
    public function testRegisterServiceThrowsExceptionIfNonExistentClass(){
    	$this->setExpectedException('\\Userdesk\Subscription\Exceptions\SubscriptionException');
		$factory = new SubscriptionFactory();
        $factory->registerService('foo', 'bar');
    }

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     */
    public function testRegisterServiceThrowsExceptionIfClassNotFulfillsContract(){
    	$this->setExpectedException('\\Userdesk\Subscription\Exceptions\SubscriptionException');
		$factory = new SubscriptionFactory();
        $factory->registerService('foo', 'Userdesk\\Subscription\\SubscriptionFactory');
    }

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     */
    public function testRegisterServiceSuccessIfClassFulfillsContract(){
		$factory = new SubscriptionFactory();
        $this->assertInstanceOf(
            'Userdesk\\Subscription\\SubscriptionFactory',
            $factory->registerService('foo', 'Userdesk\\Subscription\\Mocks\\MockService')
        );
    }

	/**
     * @covers Userdesk\Subscription\SubscriptionFactory::registerServiceAlias
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     */
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

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::createService
     * @covers Userdesk\Subscription\SubscriptionFactory::fullyQualifiedServiceName
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     * @covers Userdesk\Subscription\SubscriptionFactory::buildService
     */
    public function testCreateServiceUserRegistered(){
    	Config::set('subscription.services.foo.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $service = $factory->createService('foo');

        $this->assertNull($service);

        $factory->registerService('foo', 'Userdesk\Subscription\Mocks\MockService');

        $newService = $factory->createService('foo');

        $this->assertInstanceOf('Userdesk\\Subscription\\Contracts\\Service', $newService);
    }

    /**
     * @covers Userdesk\Subscription\SubscriptionFactory::createService
     * @covers Userdesk\Subscription\SubscriptionFactory::fullyQualifiedServiceName
     * @covers Userdesk\Subscription\SubscriptionFactory::registerService
     * @covers Userdesk\Subscription\SubscriptionFactory::buildService
     */
    public function testCreateServiceUserRegisteredOverridesPreLoaded(){
    	Config::set('subscription.services.paypal.email', 'test@best.com');

        $factory = new SubscriptionFactory();
        $factory->registerService('paypal', 'Userdesk\Subscription\Mocks\MockService');

        $service = $factory->createService('paypal');

        $this->assertInstanceOf('Userdesk\\Subscription\\Mocks\\MockService', $service);
    }
}
