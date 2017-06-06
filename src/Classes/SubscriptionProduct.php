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
     * Get Product Title.
     *
     * @return string
     */
	public function getTitle(){
		return $this->title;
	}

	/**
     * Get Product Description.
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
     * Get Product Recurrence Type.
     *
     * @return string 'year'|'month'|'day'|'none'
     */
	public function getRecurrence(){
		return $this->recurrence;
	}

	/**
     * Get Product Price.
     *
     * @return float
     */
	public function getPrice(){
		return $this->price;
	}

	/**
     * Get Product Discount.
     *
     * @return float
     */
	public function getDiscount(){
		return $this->discount;
	}

	/**
     * Get Product First Price.
     *
     * @return float
     */
	public function getFirstPrice(){
		return ($this->getDiscount() > 0)?(sprintf("%.02d", $this->getPrice() - $this->getDiscount())):$this->getPrice();
	}

	/**
     * Get Product IPN Url.
     *
     * @return string
     */
	public function getIpnUrl(){
		return $this->ipnUrl;
	}

	/**
     * Get Product Return Url.
     *
     * @return string
     */
	public function getReturnUrl(){
		return $this->returnUrl;
	}

	/**
     * Get Product Cancel Url.
     *
     * @return string
     */
	public function getCancelUrl(){
		return $this->cancelUrl;
	}
}