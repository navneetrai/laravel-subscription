<?php namespace toufee\Subscription\Classes;

use toufee\Subscription\Contracts\Consumer as SubscriptionConsumerContract;

class SubscriptionConsumer implements SubscriptionConsumerContract {

	protected $name;
	protected $address;
	protected $city;
	protected $state;
	protected $zip;
	protected $country;
	protected $email;
	protected $phone;

	/**
     * @param string $name
     * @param string $address
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $country
     * @param string $email
     * @param string $phone
     */
	public function __construct(string $name = '', string $address = '', string $city = '', string $state = '', string $zip = '', string $country = '', string $email = '', string $phone = ''){
		$this->name = $name;
		$this->address = $address;
		$this->city = $city;
		$this->state = $state;
		$this->zip = $zip;
		$this->country = $country;
		$this->email = $email;
		$this->phone = $phone;
	}

	/**
     * Create Consumer's Name.
     *
     * @return string
     */
	public function getName(){
		return $this->name;
	}

	/**
     * Create Consumer's Address.
     *
     * @return string
     */
	public function getAddress(){
		return $this->address;
	}

	/**
     * Create Consumer's City.
     *
     * @return string
     */
	public function getCity(){
		return $this->city;
	}

	/**
     * Create Consumer's State.
     *
     * @return string
     */
	public function getState(){
		return $this->state;
	}

	/**
     * Create Consumer's Zip Code.
     *
     * @return string
     */
	public function getZip(){
		return $this->zip;
	}

	/**
     * Create Consumer's Country Code.
     *
     * @return string
     */
	public function getCountry(){
		return $this->country;
	}

	/**
     * Create Consumer's Email Id.
     *
     * @return string
     */
	public function getEmail(){
		return $this->email;
	}

	/**
     * Create Consumer's Phone number.
     *
     * @return string
     */
	public function getPhone(){
		return $this->phone;
	}
}