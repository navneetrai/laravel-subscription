<?php namespace Userdesk\Subscription\Mocks;

use Userdesk\Subscription\Classes\TransactionResult;
use Userdesk\Subscription\Classes\ProcessorInfo;
use Userdesk\Subscription\Exceptions\TransactionException;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;
use Userdesk\Subscription\Contracts\Consumer as SubscriptionConsumerContract;
use Userdesk\Subscription\Contracts\Service as ProcessorContract;

class MockService implements ProcessorContract{
	private $config = [];

	public function __construct(array $config){
		$this->config = $config; 
	}

	/**
	 * Create Redirect response to complete cart.
	 *
	 * @param int $id
	 * @param \Userdesk\Subscription\Contracts\Product $product
	 * @param \Userdesk\Subscription\Contracts\Consumer $consumer
	 * @return \Illuminate\Http\Response|null
	 */
	public function complete(int $id, SubscriptionProductContract $product, SubscriptionConsumerContract $consumer = null){
		$redir_url = 'https://www.google.com';
		return redirect()->away($redir_url);
	}

	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input){
		$item_number	= str_random(12);
		$txn_id			= str_random(12);

		$action = 'test';
		$status = 'mock';
		$amount = array_get($input, 'mc_gross', 0);

		return new TransactionResult($item_number, $txn_id, $amount, $status, $action, $input);		
	}

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input){	
		$item_number			= str_random(12);
		$subscr_id			= str_random(12);

		$action = 'test';
		$status = 'mock';
		$amount = array_get($input, 'mc_gross', 0);


		return new TransactionResult($item_number, $subscr_id, 0, $payment_status, $action, $keys->get());		
			
	}

	/**
	 * Return Processor Info.
	 *
	 * @return \Userdesk\Subscription\Classes\ProcessorInfo|null
	 */
	public function info(){
		new ProcessorInfo('Mock', '/vendor/laravel-subscription/logo/mock.png', 'http://www.example.com');
	}
}