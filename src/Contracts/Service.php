<?php namespace toufee\Subscription\Contracts;

use Illuminate\Http\Request;

interface Service{
	/**
	 * Create Redirect response to complete cart.
	 *
	 * @param int $id
	 * @param \toufee\Subscription\Contracts\Product $product
	 * @param \toufee\Subscription\Contracts\Consumer $consumer
	 * @return \Symfony\Component\HttpFoundation\Response|null
	 */
	public function complete(int $id, Product $product, Consumer $consumer);

	/**
	 * Handle IPN data.
	 *
	 * @param array $input
	 * @return \toufee\Subscription\Class\TransactionResult|null
	 */
	public function ipn(array $input);

	/**
	 * Handle PDT data.
	 *
	 * @param array $input
	 * @return \toufee\Subscription\Class\TransactionResult|null
	 */
	public function pdt(array $input);
}