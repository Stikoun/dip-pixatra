<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class AccountController
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
		$context['post'] = Timber::get_post();

		Timber::render('templates/woo/account.twig', $context);
	}
}
