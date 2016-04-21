<?php namespace Userdesk\Subscription\Contracts;

use Illuminate\Http\Request;

interface Service{
	/**
	 * Create Redirect response to complete cart.
	 *
	 * @param int $id
	 * @param \Userdesk\Subscription\Contracts\Product $product
	 * @param \Userdesk\Subscription\Contracts\Consumer $consumer
	 * @return \Symfony\Component\HttpFoundation\Response|null
	 */
	public function complete(int $id, Product $product, Consumer $consumer);

	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input);

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \Userdesk\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input);
}