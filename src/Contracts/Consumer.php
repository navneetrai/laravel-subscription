<?php namespace Userdesk\Subscription\Contracts;

interface Consumer{
	/**
     * Create Consumer's Name.
     *
     * @return string
     */
	public function getName();

	/**
     * Create Consumer's Address.
     *
     * @return string
     */
	public function getAddress();

	/**
     * Create Consumer's City.
     *
     * @return string
     */
	public function getCity();

	/**
     * Create Consumer's State.
     *
     * @return string
     */
	public function getState();

	/**
     * Create Consumer's Zip Code.
     *
     * @return string
     */
	public function getZip();

	/**
     * Create Consumer's Country Code.
     *
     * @return string
     */
	public function getCountry();

	/**
     * Create Consumer's Email Id.
     *
     * @return string
     */
	public function getEmail();

	/**
     * Create Consumer's Phone number.
     *
     * @return string
     */
	public function getPhone();
}