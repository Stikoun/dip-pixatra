<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class CheckoutController
{
	public function __construct()
	{
		$this->index();
	}

	/**
	 * Display the page
	 *
	 * @return Timber\Timber
	 */
	public function index()
	{		
		$context = Timber::context();
		$context['options'] = get_fields('options');
		$context['acf'] = get_fields();

		if ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) ) {
			Timber::render('templates/woo/thank-you.twig', $context);
		}
		else {
			Timber::render('templates/woo/checkout.twig', $context);
		}
	}
}
