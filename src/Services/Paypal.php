<?php namespace Userdesk\Subscription\Services;

use Userdesk\Subscription\Classes\TransactionResult;
use Userdesk\Subscription\Classes\ProcessorInfo;
use Userdesk\Subscription\Exceptions\TransactionException;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;
use Userdesk\Subscription\Contracts\Consumer as SubscriptionConsumerContract;
use Userdesk\Subscription\Contracts\Service as ProcessorContract;

class Paypal implements ProcessorContract{
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
		$params = [
			'business'=>array_get($this->config, 'email'),
			'currency_code'=>'USD', 
			'no_shipping'=>1,
			'no_note'=>1,
			'item_name'=>$product->getTitle(),
			'item_number'=>$id, 
			'cpp_header_image'=>array_get($this->config, 'logo'), 
			'cpp_header_color'=>'FFFFFF',
			'notify_url'=>$product->getIpnUrl(),
			'return'=>$product->getReturnUrl(),
			'cancel_return'=>$product->getCancelUrl(), 
			'submit'=>'Pay Using PayPal'
		];

		$recur = $this->getRecurrenceString($product->getRecurrence());

		if($product->getRecurrence() != 'none'){
			$params = array_merge($params, [
				'cmd'=>'_xclick-subscriptions', 
				'a3'=>$product->getPrice(), 
				'p3'=>$product->getFrequency(), 
				't3'=>$recur,
				'src'=>1
			]);

			if($product->getDiscount() > 0){
				$params = array_merge($params, [
					'a1'=>$product->getFirstPrice(), 
					'p1'=>$product->getFrequency(), 
					't1'=>$recur
				]);
			}
		}else{
			$params = array_merge($params, [
				'cmd'=>'_xclick', 
				'amount'=>$product->getPrice()
			]);
		}

		$redir_url = sprintf('https://www.paypal.com/cgi-bin/webscr?%s', http_build_query($params));
		return redirect()->away($redir_url);
	}

	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input){
		$item_number	= array_get($input, 'item_number');
		if(!empty($item_number)){
			$txn_id			= array_get($input, 'txn_id', str_random(12));			
			$subscr_id		= array_get($input, 'subscr_id', $txn_id);
			$txn_type		= array_get($input, 'txn_type', 'subscr_payment');
			  
			$amount = array_get($input, 'mc_gross', 0);
			  			  
			if (preg_match ('/subscr_payment|web_accept/', $txn_type)) {
				$status = array_get($input, 'payment_status', 'Unknown');			
				if (preg_match('/Completed|Processed|Canceled\_Reversal/', $status)) {
					$action = 'payment';
				} elseif (preg_match('/Refunded|Reversed/', $status)) {
					$action = 'refund';
					$amount = -1 * abs($amount);
				} elseif (preg_match('/Created|Pending/', $status)) {
					$action = 'signup';
				}
			} else{
				$status = $txn_type;
				if (preg_match ('/expire|eot|cancel|fail/', $txn_type)) {					
					$action = 'cancel';
				} elseif (preg_match ('/signup/', $txn_type)) {
					$action = 'signup';
				}
			}

			return new TransactionResult($item_number, $txn_id, $amount, $status, $action, $input);
		}

		throw new TransactionException('Item Number Not Found for Paypal IPN');
	}

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input){
		$req		 = 'cmd=_notify-synch';
		$tx_token	 = array_get($input, 'tx');
		$auth_token  = array_get($this->config, 'auth');
		
		if(empty($auth_token)){
			throw new TransactionException('Invalid Paypal Auth Token');
		}

		$req	.= "&tx=$tx_token&at=$auth_token";

		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: ".strlen ($req)."\r\n\r\n";
		$fp		 = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		  
		if (!$fp) {
			throw new TransactionException('Cannot Connect to Paypal');
		} else {
			fputs ($fp, $header.$req);
			$res = '';
			$headerdone = false;
			
			while (!feof ($fp)) {
				$line = fgets ($fp, 1024);
				 
				if (strcmp ($line, "\r\n") == 0) {
					$headerdone = true;
				} elseif ($headerdone) {
					$res .= $line;
				}
			}

			$lines = explode ("\n", $res);

			fclose ($fp);	

			$keys = collect([]);					
								
			if (strcmp ($lines[0], "SUCCESS") == 0) {				 
				for ($i = 1; $i < count ($lines); $i++) {
					@list ($key, $val) = explode ("=", $lines[$i]);
					$keys->put(urldecode($key), urldecode($val));
				}

				$item_number	  = $keys->get('item_number');
				$payment_status   = $keys->get('payment_status');
				
				$subscr_id		  = $keys->get('subscr_id', $keys->get('txn_id'));				
				$item_name		  = $keys->get('item_name');				
				$payment_amount   = $keys->get('mc_gross');
				$txn_id			  = $keys->get('txn_id');
				$payer_email	  = $keys->get('payer_email');
				 
				$action = '';

				if(preg_match ('/^Completed|Pending$/', $payment_status)){
					$action = 'signup';					
				}

				return new TransactionResult($item_number, $subscr_id, 0, $payment_status, $action, $keys->all());
			} elseif (strcmp ($lines[0], "FAIL") == 0) {	
				throw new TransactionException("Paypal check failed");
			}
		}		
	}

	/**
	 * Return Processor Info.
	 *
	 * @return \Userdesk\Subscription\Class\ProcessorInfo|null
	 */
	public function info(){
		return new ProcessorInfo('Paypal', '/vendor/laravel-subscription/logo/paypal.png', 'https://www.paypal.com');
	}

	private function getRecurrenceString($recur){
		return ($recur=='day')?'D':(($recur=='year')?'Y':'M');
	}
}