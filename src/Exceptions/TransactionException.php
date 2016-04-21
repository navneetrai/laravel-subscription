<?php namespace toufee\Subscription\Exceptions;

use Exception;

/**
 * Generic library-level exception.
 */
class TransactionException extends Exception{
	private $message;
	private $data;

	/**
     * Create a new exception instance.
     *
     * @param  string  $data
     * @param  array  $data
     * @return void
     */
	public function __construct(string $message, array $data = []){
		$this->message = $message;
		$this->data = $data;

		parent::__construct($message);
	}
 	
 	/**
     * Get the underlying response instance.
     *
     * @return string
     */
	public function getMessage(){
		return $this->message;
	}

	/**
     * Get the underlying response instance.
     *
     * @return array
     */
	public function getData(){
		return $this->data;
	}
}