<?php

namespace App\Controllers\Templates;

use Timber\Timber;


class PageController
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
		$context['options'] = get_fields('options');
		$context['post'] = Timber::get_post();


		if ($context['post']->post_name == 'servis') {
			Timber::render('pages/service.twig', $context);
		}
		elseif ($context['post']->post_name == 'casto-kladene-otazky') {
			Timber::render('pages/faq.twig', $context);
		}
		elseif ($context['post']->post_name == 'kontakty') {
			Timber::render('pages/contact.twig', $context);
		}
		elseif ($context['post']->post_name == 'garance-kvality') {
			Timber::render('pages/quality.twig', $context);
		}
		elseif ($context['post']->post_name == 'prodejna') {
			Timber::render('pages/branch.twig', $context);
		}
		elseif ($context['post']->post_name == 'vykup') {
			Timber::render('pages/repurchase.twig', $context);
		}
		elseif ($context['post']->post_name == 'varianty-nasich-zarizeni') {
			Timber::render('pages/variations.twig', $context);
		}
		elseif (is_404()) {
			Timber::render('pages/404.twig', $context);			
		}
		else {
			Timber::render('templates/page.twig', $context);			
		}


	}
}
