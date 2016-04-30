<?php namespace Userdesk\Subscription\Classes;

class ProcessorInfo {

	protected $name;
	protected $logo;
	protected $url;

	/**
     * @param string $name
     * @param string $logo
     * @param string $url
     */
	public function __construct(string $name, string $logo, string $url){
		$this->name = $name;
		$this->logo = $logo;
		$this->url = $url;
	}

	/**
     * Get Processor name.
     *
     * @return string
     */
	public function getName(){
		return $this->name;
	}


	/**
     * Get Processor logo.
     *
     * @return string
     */
	public function getLogo(){
		return $this->logo;
	}

	/**
     * Get Processor url.
     *
     * @return string
     */
	public function getUrl(){
		return $this->url;
	}
}