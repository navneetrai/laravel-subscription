<?php namespace Userdesk\Subscription\Services;

use Userdesk\Subscription\Classes\TransactionResult;
use Userdesk\Subscription\Exceptions\TransactionException;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;
use Userdesk\Subscription\Contracts\Consumer as SubscriptionConsumerContract;
use Userdesk\Subscription\Contracts\Service as ProcessorContract;

class TwoCheckout implements ProcessorContract{
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
	public function complete(int $id, SubscriptionProductContract $product, SubscriptionConsumerContract $consumer){
		$params = [
			'mode'=>'2CO',
			'sid'=>array_get($this->config, 'sid'),
			'li_0_type'=>'product',
			'li_0_name'=>$product->getTitle(),
			'li_0__description'=>$product->getDescription(),
			'li_0_price'=>$product->getPrice(),
			'li_0_recurrence'=>($product->getFrequency()=='month') ? '1 Month' : (($product->getFrequency()=='year')?'1 Year':''),
			'li_0_duration'=>(($product->getFrequency()=='year')||($product->getFrequency()=='month')) ? 'Forever':'',
			'li_0_tangible'=>'N',
			'li_0_quantity'=>'1',
			'merchant_order_id'=>$id,
			'card_holder_name'=>$consumer->getName(),
			'street_address'=>$consumer->getAddress(),
			'street_address2'=>'',
			'city'=>$consumer->getCity(),
			'state'=>$consumer->getState(),
			'zip'=>$consumer->getZip(),
			'country'=>$consumer->getCountry(),
			'email'=>$consumer->getEmail(),
			'phone'=>$consumer->getPhone(),
			'purchase_step'=>'payment-method',
			'x_receipt_link_url'=>$product->getReturnUrl(),
			'ipn_url'=>$product->getIpnUrl(),
			'submit'=>'Checkout'
		];

		if($product->getDiscount() > 0){
			$params = array_merge($params, [
				'li_0_startup_fee'=>$product->getDiscount(), 
			]);
		}

		$url   = sprintf('https://www.2checkout.com/checkout/purchase?%s', http_build_query($params));			
		return redirect()->away($url);
	}                        
	
	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input){
		$item_number	= array_get($input, 'vendor_order_id');
		if(!empty($item_number)){		
			$txn_type		= array_get($input, 'message_type');
			$txn_id			= array_get($input, 'invoice_id', str_random(12));
			$subscr_id		= array_get($input, 'sale_id');

			$amount = array_get($input, 'invoice_usd_amount', array_get($input, 'item_usd_amount_1'));		
			  	  
			if (preg_match ('/ORDER_CREATED|FRAUD_STATUS_CHANGED|INVOICE_STATUS_CHANGED|RECURRING_INSTALLMENT_SUCCESS/', $txn_type)) {
				$fraud_status = array_get($input, 'fraud_status');

				$action = 'payment';

				if(($fraud_status=='fail')||($fraud_status=='declined')){
					$action = 'fail';
				}									
			} elseif (preg_match ('/REFUND_ISSUED/', $txn_type)) {
				$action = 'refund';
				$amount = -1 * abs($amount);
			} elseif (preg_match ('/subscr_cancel|subscr_eot|subscr_failed|RECURRING_STOPPED|RECURRING_COMPLETE|RECURRING_INSTALLMENT_FAILED/', $txn_type)) {
				$action = 'cancel';
			}

			return new TransactionResult($item_number, $txn_id, $amount, $txn_type, $action, $input);
		}

		throw new TransactionException('Cart Not Found for 2Checkout IPN', ['data'=>$input]);
	}

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input){
		$demo_order		  = array_get($input, 'demo') == 'Y';
		$subscr_id		  = $demo_order ? 0 : array_get($input, 'order_number');
		$item_number	  = array_get($input, 'merchant_order_id');
		$payment_status   = array_get($input, 'credit_card_processed');
		$payment_amount	  = array_get($input, 'total');
		$txn_id			  = array_get($input, 'order_number');
		$payer_email	  = array_get($input, 'email');
		$status 		  = 'Unknown';
		
		$key = strtoupper(md5(sprintf('%s%s%s%s', array_get($this->config, 'secret'), array_get($this->config, 'sid'), $subscr_id, $payment_amount)));		  

		if ($key == array_get($input, 'key')) {					
			if ($demo_order){
				$status = 'Demo Order';
				$action = 'demo';
			} else  {
				if(preg_match('/^Y|K$/', $payment_status)){
					$action = 'signup';
					$status = 'Success';
				}else{
					$status = 'Fail';
					$action = 'fail';
				}
			}
		}

		return new TransactionResult($item_number, $subscr_id, $payment_amount, $status, $action, $keys->get());		
	}
}