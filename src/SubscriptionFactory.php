<?php namespace Userdesk\Subscription;
use Userdesk\Subscription\Exceptions\SubscriptionException;
use Config;

class SubscriptionFactory{

	/**
     * @var array
     */
    protected $serviceClassMap = array();

    public function __construct(){
        $this->registerServiceAlias('TwoCheckout', '2checkout');
        $this->registerServiceAlias('CCNow', 'ccnow');
    }

    /**
     * Builds and returns payment services
     *
     * It will first try to build an payment service
     *
     * @param string                $serviceName Name of service to create
     *
     * @return \Userdesk\Subscription\Contracts\Service
     */

    public function registerServiceAlias($serviceName, $alias){
        $this->registerService($alias, 'Userdesk\\Subscription\\Services\\'.$serviceName);
    }

    /**
     * Builds and returns payment services
     *
     * It will first try to build an payment service
     *
     * @param string                $serviceName Name of service to create
     *
     * @return \Userdesk\Subscription\Contracts\Service
     */
	public function createService($serviceName){        
        $config = Config::get(sprintf("subscription.services.%s", $serviceName));

        if(empty($config)){
            throw new SubscriptionException(sprintf('No Config exists for Service %s.', $serviceName));
        }

		$fullyQualifiedServiceName = $this->getFullyQualifiedServiceName($serviceName);
        if (class_exists($fullyQualifiedServiceName)) {
            return $this->buildService($fullyQualifiedServiceName, $config);
        }

        return null;
	}

    /**
     * Register a custom service to classname mapping.
     *
     * @param string $serviceName Name of the service
     * @param string $className   Class to instantiate
     *
     * @return SubscriptionFactory
     *
     * @throws Exception If the class is nonexistent or does not implement a valid ServiceInterface
     */
    public function registerService($serviceName, $className)    {
        if (!class_exists($className)) {
            throw new SubscriptionException(sprintf('Service class %s does not exist.', $className));
        }
        $reflClass = new \ReflectionClass($className);
        
        if ($reflClass->implementsInterface('Userdesk\\Subscription\\Contracts\\Service')) {
            $this->serviceClassMap[ucfirst($serviceName)] = $className;
            return $this;
        }
        
        throw new SubscriptionException(sprintf('Service class %s must implement ServiceInterface.', $className));
    }

	/**
     * Gets the fully qualified name of the service
     *
     * @param string $serviceName The name of the service of which to get the fully qualified name
     *
     * @return string The fully qualified name of the service
     */
    private function getFullyQualifiedServiceName($serviceName) {
        $serviceName = ucfirst($serviceName);
        if (isset($this->serviceClassMap[$serviceName])) {
            return $this->serviceClassMap[$serviceName];
        }
        return '\\Userdesk\\Subscription\\Services\\' . $serviceName;
    }

    /**
     * Builds payment services
     *
     * @param string                $serviceName The fully qualified service name
     *
     * @return PaymentInterface
     */
    private function buildService($serviceName, $config) {
        return new $serviceName($config);
    }
}