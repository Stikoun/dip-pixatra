<?php

namespace App\Extensions\CustomPostTypes;

class Testimonial
{

	public function __construct()
	{
        // Register Post Type Init WP action
		add_action('init', [ $this, 'registerPostType' ]);
	}

	/**
	 * Registers custom post type
	 *
	 * @return void
	 */
	public function registerPostType()
	{
        $labels = [
            'name'                  => 'Reference',
            'singular_name'         => 'Reference',
            'menu_name'             => 'Reference',
            'name_admin_bar'        => 'Reference',
            'archives'              => 'Archív',
            'attributes'            => 'Atributy',
            'parent_item_colon'     => 'Hlavní reference',
            'all_items'             => 'Všechny reference',
            'add_new_item'          => 'Přidat novou referenci',
            'add_new'               => 'Přidat referenci',
            'new_item'              => 'Nová reference',
            'edit_item'             => 'Upravit referenci',
            'update_item'           => 'Aktualizovat referenci',
            'view_item'             => 'Zobrazit referenci',
            'view_items'            => 'Zobrazit reference',
            'search_items'          => 'Vyhledat služub',
            'insert_into_item'      => 'Vložit do stránky',
            'uploaded_to_this_item' => 'Nahrané k této stránce',
            'items_list'            => 'Seznam referencí',
            'filter_items_list'     => 'Filtr',
        ];

        $args = [
            'label'               => 'Reference',
            'description'         => '',
            'labels'              => $labels,
            'supports'            => ['title', 'editor' ,'thumbnail', 'revisions'],
            'taxonomies'          => [],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 3,
            'menu_icon'           => 'dashicons-id-alt',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        ];

        register_post_type('testimonial', $args);
	}
}
