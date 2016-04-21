<?php namespace Userdesk\Subscription;

class Subscription{
	/**
     * @var SubscriptionFactory
     */
	private $subscriptionFactory;

	/**
     * Constructor
     *
     * @param SubscriptionFactory $subscriptionFactory - (Dependency injection) If not provided, a SubscriptionFactory instance will be constructed.
     */
    public function __construct(SubscriptionFactory $subscriptionFactory = null)  {
        if (null === $subscriptionFactory){
            // Create the service factory
            $subscriptionFactory = new SubscriptionFactory();
        }
        $this->subscriptionFactory = $subscriptionFactory;
    }

	/**
     * @param  string $service
     *
     * @return \Userdesk\Subscription\Contracts\Service
     */

	public function processor($service){
		return $this->subscriptionFactory->createService($service);
	}	
}