<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class CartController
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

		Timber::render('templates/woo/cart.twig', $context);
	}
}
