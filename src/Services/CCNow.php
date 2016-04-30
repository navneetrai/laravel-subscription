<?php namespace Userdesk\Subscription\Services;

use Userdesk\Subscription\Classes\TransactionResult;
use Userdesk\Subscription\Classes\ProcessorInfo;
use Userdesk\Subscription\Exceptions\TransactionException;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;
use Userdesk\Subscription\Contracts\Consumer as SubscriptionConsumerContract;
use Userdesk\Subscription\Contracts\Service as ProcessorContract;

class CCNow implements ProcessorContract{
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
		$login = array_get($this->config, 'login');
		$key   = array_get($this->config, 'key');
		$hash  = md5(sprintf("%s^x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code^%s^%s^USD^%s", $login, $id, $product->getPrice(), $key));

		$params = [
			'x_version'=>'1.0',
			'x_fp_arg_list'=>'x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code',
			'x_currency_code'=>'USD',
			'x_method'=>'CC',
			'x_shipping_amount'=>'0.00',
			'x_subscription_type'=>'A',
			'x_login'=>array_get($this->config, 'login'),
			'x_fp_sequence'=>$id,
			'x_invoice_num'=>$id,
			'x_ship_to_name'=>$consumer->getName(),
			'x_ship_to_address'=>$consumer->getAddress(),
			'x_ship_to_address2'=>'',
			'x_ship_to_city'=>$consumer->getCity(),
			'x_ship_to_state'=>$consumer->getState(),
			'x_ship_to_zip'=>$consumer->getZip(),
			'x_ship_to_country'=>$consumer->getCountry(),
			'x_ship_to_phone'=>$consumer->getPhone(),
			'x_email'=>$consumer->getEmail(),
			'x_product_sku_1'=>$id,
			'x_product_title_1'=>$product->getTitle(),
			'x_product_quantity_1'=>'1',
			'x_product_unitprice_1'=>$product->getPrice(),
			'x_product_url_1'=>array_get($this->config, 'url'),
			'x_amount'=>$product->getPrice(),
			'x_subscription_freq'=>($product->getFrequency()=='month') ? '1M' : (($product->getFrequency()=='year')?'1Y':''),
			'x_fp_hash'=>$hash
		];

		$url   = sprintf('https://www.ccnow.com/cgi-local/transact.cgi?%s', http_build_query($params));			
		return redirect()->away($url);
	}
	
	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input){
		$item_number    = array_get($input, 'x_invoice_num');
		if(!empty($item_number)){			
			$txn_id         = str_random(12);
			$subscr_id      = array_get($input, 'x_orderid');
			$amount         = array_get($input, 'x_amount_usd', array_get($input, 'x_amount'));
			$status     = array_get($input, 'x_status');			
			
			if (preg_match ('/pending|shipped|chargeback\_reversal/', $status)) {
				$action = 'payment';
			} elseif (preg_match ('/refunded|chargeback|partial\_refund/', $status)) {
				$action = 'refund';
				$amount = -1 * abs($amount);
			} elseif (preg_match ('/received/', $status)) {
				$action = 'signup';
			} elseif (preg_match ('/canceled|declined|rejected/', $status)) {
				$action = 'cancel';
			}			

			return new TransactionResult($item_number, $txn_id, $amount, $status, $action, $input);
		}

		throw new TransactionException('Cart Not Found for CCNow IPN', ['data'=>$input]);
	}

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input){
		$item_number 	= array_get($input, 'cart_id');
		$subscr_id   = array_get($input, 'order_id');
		$payment_amount	 = array_get($input, 'amount');

		return new TransactionResult($item_number, $subscr_id, $payment_amount, 'Pass', 'signup', $input);
	}

	/**
	 * Return Processor Info.
	 *
	 * @return \Userdesk\Subscription\Class\ProcessorInfo|null
	 */
	public function info(){
		return new ProcessorInfo('CCNow', '/vendor/laravel-subscription/logo/ccnow.png', 'http://www.ccnow.com')
	}
}