<?php

namespace App\Hooks;

class Theme 
{
    public function __construct()
    {
        $this->themeSettings();
    }

    public function themeSettings() 
    {
        add_action('after_setup_theme', function () {
			// Support for <title>
			add_theme_support('title-tag');

            // Post Thumbnails
            add_theme_support('post-thumbnails');

            // Support for theme navigation
            add_theme_support('menus');

            // WooCommerce
            add_theme_support( 'woocommerce' );

            //Yoast SEO breadcrumbs
            add_theme_support( 'yoast-seo-breadcrumbs' );
		});

        // Custom navigation
        add_action('init', function () {
            register_nav_menu('navigation', 'Navigace');
            register_nav_menu('topbar', 'Horní menu');
            register_nav_menu('footer-left', 'Patička - levý sloupec');
            register_nav_menu('footer-right', 'Patička - pravý sloupec');
            register_nav_menu('my-account', 'Můj účet');
        });
    }
}
