<?php namespace Userdesk\Subscription\Classes;

use Userdesk\Subscription\Contracts\Product as SubscriptionProductContract;

class SubscriptionProduct implements SubscriptionProductContract {

	protected $title;
	protected $description;
	protected $frequency;
	protected $recurrence;
	protected $price;
	protected $discount;
	protected $ipnUrl;
	protected $returnUrl;
	protected $cancelUrl;

	/**
     * @param string $title
     * @param string $price
     * @param string $description
     * @param string $ipnUrl
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param string $frequency
     * @param string $recurrence 'year'|'month'|'day'|'none'
     * @param string $discount
     */
	public function __construct(string $title, float $price, string $description = '', string $ipnUrl = '', string $returnUrl = '', string $cancelUrl = '', int $frequency = 1, string $recurrence = 'month', float $discount = 0){
		$this->title = $title;
		$this->price = $price;
		$this->ipnUrl = $ipnUrl;
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl;
		$this->discount = $discount;
		$this->description = $description;
		$this->frequency = $frequency;
		$this->recurrence = $recurrence;
	}

	/**
     * Create Product Title.
     *
     * @return string
     */
	public function getTitle(){
		return $this->title;
	}

	/**
     * Create Product Description.
     *
     * @return string
     */
	public function getDescription(){
		return $this->description;
	}

	/**
     * Create Product Recurrence Frequency.
     *
     * @return string
     */
	public function getFrequency(){
		return $this->frequency;
	}

	/**
     * Create Product Recurrence Type.
     *
     * @return string 'year'|'month'|'day'|'none'
     */
	public function getRecurrence(){
		return $this->recurrence;
	}

	/**
     * Create Product Price.
     *
     * @return float
     */
	public function getPrice(){
		return $this->price;
	}

	/**
     * Create Product Discount.
     *
     * @return float
     */
	public function getDiscount(){
		return $this->discount;
	}

	/**
     * Create Product Country Code.
     *
     * @return string
     */
	public function getIpnUrl(){
		return $this->ipnUrl;
	}

	/**
     * Create Product Email Id.
     *
     * @return string
     */
	public function getReturnUrl(){
		return $this->returnUrl;
	}

	/**
     * Create Product Phone number.
     *
     * @return string
     */
	public function getCancelUrl(){
		return $this->cancelUrl;
	}
}