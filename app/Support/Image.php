<?php

/**
 * Outputs full URL to a file in assets folder
 * @param string    $file   Path to the file within /assets/ folder
 */
function dh_get_asset($file)
{
    $append = get_stylesheet_directory_uri() . '/assets/';
    return $append . $file;
}

/**
 * Outputs SVG file icon on the website with custom args
 * @param  string $name name of file
 * @param  array  $args SVG HTML arguments
 */
function dh_svg($name, $args = [])
{
    $path = dirname(__DIR__, 2) . '/assets/svg/' . $name . '.svg';
    
    $args_defaults = [
        'class' => 'text-current',
        'style' => ''
    ];

    $args = wp_parse_args($args, $args_defaults);

    $args['class'] = (is_array($args['class']) ? implode(' ', $args['class']) : $args['class']);

    if (file_exists($path)) {
        $svg = file_get_contents($path);
        //$svg = preg_replace( '/(width|height)="[\d\.]+"/i', '', $svg );
        $svg = str_replace('<svg ', '<svg class="' . esc_attr($args['class']) . '" style="' . esc_attr($args['style']) . '" ', $svg);

        return $svg;
    }

    return __('Image not found');
}
