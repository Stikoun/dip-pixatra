<?php

namespace App\Hooks;

use \Timber;
use Twig\TwigFunction;
use Twig\TwigFilter;

class Twig
{
	public function __construct()
	{
		/**
		 * Extends Timber with custom functions and its context class
		 */
		$this->extendTimber();

		/**
		 * Adds Timber to Nette Debugbar
		 */
		$this->netteDebugBar();
	}

	/**
	 * Extends Timber with custom functions and its context class
	 */
	public function extendTimber()
	{
		/**
		 * Extend Functions
		 */
		add_filter('timber/twig/functions', function ($twig) {
			$twig->addFunction(new TwigFunction('asset', 'dh_get_asset'));
			$twig->addFunction(new TwigFunction('svg', 'dh_svg'));
			$twig->addFunction(new TwigFunction('bdump', 'bdump'));
			$twig->addFunction(new TwigFunction('var_dump', 'var_dump'));
			$twig->addFunction(new TwigFunction('get_field', 'get_field'));
			$twig->addFunction(new TwigFunction('breadcrumbs', 'display_breadcrumbs'));

			return $twig;
		});

		/**
		 * Extend Context
		 */
		add_filter('timber/context', function ($context) {
			$context['cart_count'] = WC()->cart->get_cart_contents_count();
			$context['current_user'] = wp_get_current_user();

			$context['navigation'] = new Timber\Menu( 'navigation' );
			$context['topbar'] = new Timber\Menu( 'topbar' );
			$context['footer_left'] = new Timber\Menu( 'footer-left' );
			$context['footer_right'] = new Timber\Menu( 'footer-right' );
			$context['my_account'] = new Timber\Menu( 'my-account' );

			return $context;
		});
	}

	/**
	 * Adds Timber to Nette Debugbar
	 */
	public function netteDebugBar()
	{
		add_filter('timber/calling_php_file', function ($file) {
			bdump($file, 'Timber called in PHP file');
			return $file;
		});

		add_filter('timber/render/file', function ($file) {
			bdump($file, 'Twig File');
			return $file;
		});

		add_filter('timber/loader/render_data', function ($data) {
			bdump($data, 'Timber Context');
			return $data;
		});
	}
}
