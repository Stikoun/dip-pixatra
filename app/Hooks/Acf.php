<?php

namespace App\Hooks;

class Acf
{
    public function __construct()
    {
        /**
         * Add theme options page
         */
        $this->registerOptionsPage();

        /**
         * Modify ACF output
         */
        // $this->modifyAcfOutputs();
    }

    /**
     * Adds theme options page
     */
    public function registerOptionsPage()
    {
        add_action('acf/init', function () {
            acf_add_options_page([
                'page_title' => 'Nastavení webu',
                'menu_title' => 'Nastavení webu',
                'menu_slug'  => 'theme-settings_cs',
                'capability' => 'manage_options',
                'update_button' => 'Aktualizovat',
            ]);
        });
    }

    // public function modifyAcfOutputs() {
    //     add_filter('acf/format_value/type=wysiwyg', [$this, 'formatAcfField'], 100);
    // }

    // public function formatAcfField($value) {
    //     return strip_tags($value, '<br><span>');
    // }
}
