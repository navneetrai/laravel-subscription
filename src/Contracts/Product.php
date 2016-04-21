<?php namespace Userdesk\Subscription\Contracts;

interface Product{
	/**
     * Create Product Title.
     *
     * @return string
     */
	public function getTitle();

	/**
     * Create Product Description.
     *
     * @return string
     */
	public function getDescription();

	/**
     * Create Product Recurrence Frequency.
     *
     * @return string
     */
	public function getFrequency();

	/**
     * Create Product Recurrence Type.
     *
     * @return string
     */
	public function getRecurrence();

	/**
     * Create Product Price.
     *
     * @return float
     */
	public function getPrice();

	/**
     * Create Product Discount.
     *
     * @return float
     */
	public function getDiscount();

	/**
     * Create Product Country Code.
     *
     * @return string
     */
	public function getIpnUrl();

	/**
     * Create Product Email Id.
     *
     * @return string
     */
	public function getReturnUrl();

	/**
     * Create Product Phone number.
     *
     * @return string
     */
	public function getCancelUrl();
}