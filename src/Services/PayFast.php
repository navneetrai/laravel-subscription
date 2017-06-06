<?php namespace Userdesk\Subscription\Services;

use Userdesk\Subscription\Classes\TransactionResult;
use Userdesk\Subscription\Classes\ProcessorInfo;
use Userdesk\Subscription\Exceptions\TransactionException;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;
use Userdesk\Subscription\Contracts\Consumer as SubscriptionConsumerContract;
use Userdesk\Subscription\Contracts\Service as ProcessorContract;

class PayFast implements ProcessorContract{
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
        $sandbox  = array_get($this->config, 'sandbox', 0);
		$params = [
           // Merchant details
          'merchant_id' => array_get($this->config, 'merchant_id'),
          'merchant_key' => array_get($this->config, 'merchant_key'),
          'return_url' => $product->getReturnUrl(),
          'cancel_url' => $product->getCancelUrl(),
          'notify_url' => $product->getIpnUrl(),
           // Buyer details
	  	  'name_first' => $consumer->getFirstName(),
	      'name_last'  => $consumer->getLastName(),
	  	  'email_address'=> $sandbox?'sbtu01@payfast.co.za':$consumer->getEmail(),
	  	  //'cell_number'=>$consumer->getPhone(),
           // Transaction details
          'm_payment_id' => $id, //Unique payment ID to pass through to notify_url
	  	  'amount' => $product->getFirstPrice(),
          'item_name' => $product->getTitle(),
          'item_description' => $product->getDescription()
        ];   

		$frequency = $product->getFrequency();
		$recurrence = $product->getRecurrence();

		if(!$sandbox && ($recurrence != 'none')){
			$params = array_merge($params, [
			    'subscription_type'=>1,
				'billing_date' => gmdate('Y-m-d', strtotime("+".$frequency." ".$recurrence)),
                'recurring_amount'=>$product->getPrice(),
				'frequency'=>($recurrence=='month') ? (($frequency==1)?3:(($frequency==3)?4:(($frequency==6)?5:0))) : (($recurrence=='year') ? 6:0),
                'cycles'=>0
			]);
		}

        $pfOutput = '';

		// Create GET string
      	foreach( $params as $key => $val ){
          	if(isset($val)){
          		$pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
          	}
  		}


  		$passPhrase = array_get($this->config, 'passphrase', '');
	    
	    // Remove last ampersand
	    $getString = substr( $pfOutput, 0, -1 );
	    if( !empty( $passPhrase ) ){
	        $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
	    }

      	$params['signature'] = md5( $getString );

      	$pfHost = $sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

		$redir_url = sprintf('https://%s/eng/process?%s', $pfHost, http_build_query($params));
		return redirect()->away($redir_url);
	}

	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input){
		$sandbox  = array_get($this->config, 'sandbox', 0);
	     
	    // Variable initialization
	    $pfError = false;
	    $passPhrase = array_get($this->config, 'passphrase', '');
	   
	    $pfParamString = '';
	    $pfHost = $sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
	     
	    //// Dump the submitted variables and calculate security signature
	    if( !$pfError ) {	     
	        $keys = collect([]);
	        // Strip any slashes in data
	        foreach( $input as $key => $val ){
	        	$keys->put($key, stripslashes($val));
	        	if( $key != 'signature' ){
	             	$pfParamString .= $key .'='. urlencode( $val ) .'&';
	           	}
	        }
	     
	        // Remove the last '&' from the parameter string
	        $pfParamString = substr( $pfParamString, 0, -1 );
	        $pfTempParamString = $pfParamString;	         
	       
	        if( !empty( $passPhrase ) ) {
	            $pfTempParamString .= '&passphrase='.urlencode( $passPhrase );
	        }


	        $signature = md5( $pfTempParamString );
	     
	        if(empty($input['signature'])||( $input['signature'] != $signature )){
	        	$pfError = true;
	        	throw new TransactionException("Payfast Security signature mismatch");
	        }	        
	    }
	     
	    //// Verify source IP
	    /*if( !$pfError )  {
	        $validHosts = array(
	            'www.payfast.co.za',
	            'sandbox.payfast.co.za',
	            'w1w.payfast.co.za',
	            'w2w.payfast.co.za',
	            );
	     
	        $validIps = array();
	     
	        foreach( $validHosts as $pfHostname ) {
	            $ips = gethostbynamel( $pfHostname );
	     
	            if( $ips !== false )
	                $validIps = array_merge( $validIps, $ips );
	        }
	     
	        // Remove duplicates
	        $validIps = array_unique( $validIps );

	        if( !in_array( $_SERVER['REMOTE_ADDR'], $validIps ) ) {
	            $pfError = true;
	            throw new TransactionException("Payfast Bad source IP address");
	        }
	    }*/
	     
	    //// Connect to server to validate data received
	    if( !$pfError ) {
	        $output = '';
	        // Use cURL (If it's available)
	        if( function_exists( 'curl_init' ) ) {
	            $output .= "\n\nUsing cURL\n\n"; // DEBUG
	     
	            // Create default cURL object
	            $ch = curl_init();
	     
	            // Base settings
	            $curlOpts = array(
	                // Base options
	                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)', // Set user agent
	                CURLOPT_RETURNTRANSFER => true,  // Return output as string rather than outputting it
	                CURLOPT_HEADER => false,         // Don't include header in output
	                CURLOPT_SSL_VERIFYHOST => 2,
	                CURLOPT_SSL_VERIFYPEER => false,
	     
	                // Standard settings
	                CURLOPT_URL => 'https://'. $pfHost . '/eng/query/validate',
	                CURLOPT_POST => true,
	                CURLOPT_POSTFIELDS => $pfParamString,
	            );
	            curl_setopt_array( $ch, $curlOpts );
	     
	            // Execute CURL
	            $res = curl_exec( $ch );
	            curl_close( $ch );
	     
	            if( $res === false ) {
	                $pfError = true;
	                throw new TransactionException("An error occurred executing cURL");
	            }
	        }
	        // Use fsockopen
	        else
	        {
	            $output .= "\n\nUsing fsockopen\n\n"; // DEBUG
	     
	            // Construct Header
	            $header = "POST /eng/query/validate HTTP/1.0\r\n";
	            $header .= "Host: ". $pfHost ."\r\n";
	            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	            $header .= "Content-Length: " . strlen( $pfParamString ) . "\r\n\r\n";
	     
	            // Connect to server
	            $socket = fsockopen( 'ssl://'. $pfHost, 443, $errno, $errstr, 10 );
	     
	            // Send command to server
	            fputs( $socket, $header . $pfParamString );
	     
	            // Read the response from the server
	            $res = '';
	            $headerDone = false;
	     
	            while( !feof( $socket ) )
	            {
	                $line = fgets( $socket, 1024 );
	     
	                // Check if we are finished reading the header yet
	                if( strcmp( $line, "\r\n" ) == 0 ){
	                    // read the header
	                    $headerDone = true;
	                }
	                // If header has been processed
	                else if( $headerDone )
	                {
	                    // Read the main response
	                    $res .= $line;
	                }
	            }
	        }
	    }

	    //// Interpret the response from server
	    if( !$pfError ) {
	    	$lines = explode( "\n", $res );
	        $result = trim( $lines[0] );	     
	        
	        if( strcmp( $result, 'VALID' ) == 0 ){
	            $item_number	  = $keys->get('m_payment_id');
	            if(!empty($item_number)){
					$payment_status   = $keys->get('payment_status');
					
					$item_name		  = $keys->get('item_name');				
					$payment_amount   = $keys->get('amount_gross');
					$txn_id			  = $keys->get('pf_payment_id');
					$payer_email	  = $keys->get('email_address');
					 
					$action = '';

					if($payment_status == 'COMPLETE'){
						$action = 'payment';					
					}else if($payment_status == 'CANCELLED'){
						$action = 'cancel';					
					}

					return new TransactionResult($item_number, $txn_id, $payment_amount, $payment_status, $action, $keys->all());
				}else{
					throw new TransactionException('Item Number Not Found for Payfast IPN');
				}
	        }else{
	            // Log for investigation
	            $pfError = true;
	            throw new TransactionException("The data received is invalid");
	        }
	    }	
	}

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input){
		$sandbox  = array_get($this->config, 'sandbox', 0);
     
		// Variable Initialization
		$pmtToken = isset( $input['pt'] ) ? $input['pt'] : null;
		     
		if( !empty( $pmtToken ) )   {
	        // Variable Initialization
	        $error = false;
	        $authToken = array_get($this->config, 'auth_token', '');
	        $req = 'pt='. $pmtToken .'&at='. $authToken;
	        $host = $sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
	     
	        //// Connect to server
	        if( !$error )
	        {
	            // Construct Header
	            $header = "POST /eng/query/fetch HTTP/1.0\r\n";
	            $header .= 'Host: '. $host ."\r\n";
	            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	            $header .= 'Content-Length: '. strlen( $req ) ."\r\n\r\n";
	     
	            // Connect to server
	            $socket = fsockopen( 'ssl://'. $host, 443, $errno, $errstr, 10 );
	     
	            if( !$socket )
	            {
	                $error = true;
	                print( 'errno = '. $errno .', errstr = '. $errstr );
	            }
	        }
	     
	        //// Get data from server
	        if( !$error )
	        {
	            // Send command to server
	            fputs( $socket, $header . $req );
	     
	            // Read the response from the server
	            $res = '';
	            $headerDone = false;
	     
	            while( !feof( $socket ) )
	            {
	                $line = fgets( $socket, 1024 );
	     
	                // Check if we are finished reading the header yet
	                if( strcmp( $line, "\r\n" ) == 0 )
	                {
	                    // read the header
	                    $headerDone = true;
	                }
	                // If header has been processed
	                else if( $headerDone )
	                {
	                    // Read the main response
	                    $res .= $line;
	                }
	            }

	     
	            // Parse the returned data
	            $lines = explode( "\n", $res );
	        }
	     
	        //// Interpret the response from server
	        if( !$error )
	        {
	            $result = trim( $lines[0] );
	     
	            // If the transaction was successful
	            if( strcmp( $result, 'SUCCESS' ) == 0 ){
	                $keys = collect([]);
	                // Process the reponse into an associative array of data
	                for( $i = 1; $i < count( $lines ); $i++ ){
	                    if(strpos($lines[$i], "=")>0){
                            list( $key, $val ) = explode( "=", $lines[$i] );
                            $keys->put(urldecode($key), urldecode($val));
                        }
	                }
	            }
	            // If the transaction was NOT successful
	            else if( strcmp( $result, 'FAIL' ) == 0 )
	            {
	                // Log for investigation
	                $error = true;
	                // 
	            }
	        }

            // Close socket if successfully opened
            if( $socket )
                fclose( $socket );

	     
	        if( !$error ){
	            $item_number	  = $keys->get('m_payment_id');
				$payment_status   = $keys->get('payment_status');
				
				$subscr_id		  = $keys->get('subscr_id', $keys->get('pf_payment_id'));				
				$item_name		  = $keys->get('item_name');				
				$payment_amount   = $keys->get('amount_gross');
				$txn_id			  = $keys->get('pf_payment_id');
				$payer_email	  = $keys->get('email_address');
				 
				$action = '';

				if($payment_status == 'COMPLETE'){
					$action = 'signup';					
				}

				return new TransactionResult($item_number, $subscr_id, 0, $payment_status, $action, $keys->all());
	        } else {	
				throw new TransactionException("Payfast PDT check failed");
			}

	    }		
	}

	/**
	 * Return Processor Info.
	 *
	 * @return \Userdesk\Subscription\Class\ProcessorInfo|null
	 */
	public function info(){
		return new ProcessorInfo('PayFast', '/vendor/laravel-subscription/logo/payfast.png', 'https://www.payfast.co.za');
	}

	private function getRecurrenceString($recur){
		return ($recur=='day')?'D':(($recur=='year')?'Y':'M');
	}
}