<?php namespace Userdesk\Subscription\Contracts;

interface Product{
	/**
     * Get Product Title.
     *
     * @return string
     */
	public function getTitle();

	/**
     * Get Product Description.
     *
     * @return string
     */
	public function getDescription();

	/**
     * Get Product Recurrence Frequency.
     *
     * @return string
     */
	public function getFrequency();

	/**
     * Get Product Recurrence Type.
     *
     * @return string
     */
	public function getRecurrence();

	/**
     * Get Product Price.
     *
     * @return float
     */
	public function getPrice();

	/**
     * Get Product Discount.
     *
     * @return float
     */
	public function getDiscount();

     /**
     * Get Product First Price.
     *
     * @return float
     */
     public function getFirstPrice();

	/**
     * Get Product Country Code.
     *
     * @return string
     */
	public function getIpnUrl();

	/**
     * Get Product Email Id.
     *
     * @return string
     */
	public function getReturnUrl();

	/**
     * Get Product Phone number.
     *
     * @return string
     */
	public function getCancelUrl();
}