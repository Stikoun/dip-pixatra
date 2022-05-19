<?php

namespace App\Hooks;

class Admin 
{
    public function __construct()
    {
        $this->adminSettings();
    }

    public function adminSettings() 
    {
        // Custom Admin Bar
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            global $post;

            if (is_admin()) {
                $screen = get_current_screen();
            }

            $homepage_href = get_home_url();

            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('site-name');
            $wp_admin_bar->remove_node('customize');
            $wp_admin_bar->remove_node('updates');
            $wp_admin_bar->remove_node('comments');
            $wp_admin_bar->remove_node('new-content');
            $wp_admin_bar->remove_node('search');
            $wp_admin_bar->remove_node('edit');
            $wp_admin_bar->remove_node('preview');
            $wp_admin_bar->remove_node('view');
            $wp_admin_bar->remove_node('w3tc');
            $wp_admin_bar->remove_node('wpss-cache-purge');
            $wp_admin_bar->remove_node('my-account');
            $wp_admin_bar->remove_node('archive');

            $wp_admin_bar->add_node([
                'id'     => 'logout',
                'title'  => sprintf('<span class="ab-icon dashicons-before dashicons-migrate" style="padding: 7px 0;"> </span> %s', __('Odhlásit se', 'novat')),
                'parent' => 'top-secondary',
                'href'   => wp_logout_url(),
            ]);

            if (is_admin()) {
                $wp_admin_bar->add_node([
                    'id'    => 'go-home',
                    'title' => sprintf('<span class="ab-icon dashicons-before dashicons-admin-home" style="padding: 7px 0;"> </span> %s', get_bloginfo('name')),
                    'href'  => $homepage_href,
                    'meta'  => ['class' => 'dh-toolbar-go-home'],
                ]);

                if ((isset($post->ID)) && ($screen->base == 'post') && ($post->post_status == 'publish')) {
                    $wp_admin_bar->add_node([
                        'id'    => 'view-post',
                        'title' => '<span class="ab-icon dashicons-before dashicons-admin-post" style="padding: 7px 0;"> </span> Zobrazit článek',
                        'href'  => get_permalink($post->ID),
                        'meta'  => ['class' => 'dh-toolbar-admin'],
                    ]);
                }

            } else {
                $wp_admin_bar->add_node([
                    'id'    => 'go-home',
                    'title' => sprintf('<span class="ab-icon dashicons-before dashicons-wordpress" style="padding: 7px 0;"> </span> %s', __('WP Administrace', 'novat')),
                    'href'  => admin_url('/'),
                    'meta'  => ['class' => 'dh-toolbar-admin'],
                ]);

                if (is_singular()) {
                    $wp_admin_bar->add_node([
                        'id'    => 'edit-post',
                        'title' => '<span class="ab-icon dashicons-before dashicons-edit" style="padding: 7px 0;"> </span> Upravit stránku',
                        'href'  => get_edit_post_link($post->ID),
                        'meta'  => ['class' => 'dh-toolbar-admin'],
                    ]);
                }
            }
        }, 999999);
    }
}