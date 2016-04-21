<?php namespace Userdesk\Subscription\Classes;

class TransactionResult {

	protected $id;
	protected $ident;
	protected $amount;
	protected $status;
	protected $action;
	protected $data;

	/**
     * @param int $id
     * @param string $ident
     * @param float $amount
     * @param string $status
     * @param string $action payment|refund|cancel|signup|fail|demo
     * @param array $data
     */
	public function __construct(int $id, string $ident, float $amount, string $status, string $action, array $data){
		$this->id = $id;
		$this->ident = $ident;
		$this->amount = $amount;
		$this->status = $status;
		$this->action = $action;
		$this->data = $data;
	}

	/**
     * Get Transaction Result Id. Should be same as id sent to complete function
     *
     * @return int
     */
	public function getId(){
		return $this->id;
	}

	/**
     * Get Transaction Ident.
     *
     * @return string
     */
	public function getIdent(){
		return $this->ident;
	}

	/**
     * Get Transaction Amount.
     *
     * @return float
     */
	public function getAmount(){
		return $this->amount;
	}

	/**
     * Get Transaction Result Status.
     *
     * @return string
     */
	public function getStatus(){
		return $this->status;
	}

	/**
     * Get Transaction Result Name Action.
     *
     * @return string payment|refund|cancel|signup|fail|demo
     */
	public function getAction(){
		return $this->action;
	}

	/**
     * Get Transaction Result Data.
     *
     * @return array
     */
	public function getData(){
		return $this->data;
	}
}