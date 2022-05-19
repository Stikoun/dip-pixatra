<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class HomeController
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
        $context['acf'] = get_fields();

		Timber::render('templates/home.twig', $context);
	}
}
