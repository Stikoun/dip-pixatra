<?php

namespace App\Hooks;

class Yoast 
{
    public function __construct()
    {
        $this->yoastSettings();
    }

    public function yoastSettings() 
    {
        add_filter('wpseo_breadcrumb_separator', [$this, 'breadcrumbSeparator'], 10, 1);

        add_filter( 'wpseo_breadcrumb_single_link', [$this, 'homeIcon'], 10 ,2);
    }

    public function breadcrumbSeparator() {
        return dh_svg('right-arrow', ['class' => 'inline-block h-3 fill-gray w-3 mx-1 md:mx-3']);
    }

    public function homeIcon($link_output , $link) {
        if ($link['text'] == 'Domů') {
            $link_output = '<a href="'.$link['url'].'">' . dh_svg('home', ['class' => 'inline-block h-3 w-3 fill-gray md:h-5 md:w-5']) . '</a>';
        }

        return $link_output;
    }
}