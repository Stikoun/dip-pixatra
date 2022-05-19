<?php

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

$configFolder = glob(__DIR__ . '/app/Config/*.php');
foreach ($configFolder as $configFile) {
    require($configFile);
}

$supportFolder = glob(__DIR__ . '/app/Support/*.php');
foreach ($supportFolder as $supportFile) {
    require($supportFile);
}


// Initialize Timber.
new Timber\Timber();
Timber::$dirname = 'app/Views';


new App\Hooks\Admin();
new App\Hooks\Theme();
new App\Hooks\Twig();
new App\Hooks\Acf();
new App\Hooks\Yoast();

new App\Extensions\CustomPostTypes\Testimonial();


/**
 * Assign global $product object in Timber
 */
function timber_set_product( $post ) {
    global $product;
    $product = isset( $post->product ) ? $post->product : wc_get_product( $post->ID );
}

function getCategoryThumbnail ($term_id) {
    $thumbnail_id = get_woocommerce_term_meta( $term_id, 'thumbnail_id', true );
    $image = wp_get_attachment_url( $thumbnail_id );

    return $image;
}

// Remove woocommerce-smallscreen.css
function removeCss( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	return $enqueue_styles;
}
add_filter( 'woocommerce_enqueue_styles', 'removeCss' );

function themesharbor_disable_woocommerce_block_styles() {
    wp_dequeue_style( 'wc-blocks-style' );
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
  }
add_action( 'wp_enqueue_scripts', 'themesharbor_disable_woocommerce_block_styles' );


remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts() {
  echo '<style>
    #edittag {
        max-width: initial !important;
    }
  </style>';
}



/**
 * Handle a custom 'customvar' query var to get products with the 'customvar' meta.
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Product_Query.
 * @return array modified $query
 */
function handle_custom_query_var( $query, $query_vars ) {
	if ( ! empty( $query_vars['attribute'] ) ) {
		$query['meta_query'][] = array(
			'key' => 'customvar',
			'value' => esc_attr( $query_vars['attributes'] ),
		);
	}

	return $query;
}
add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'handle_custom_query_var', 10, 2 );

/** to change the position of excerpt **/
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 6 );

add_filter( 'dgwt/wcas/labels', function ( $labels ) {
    $labels['in'] = 'v:'; 
    return $labels;
} );

add_filter('wpseo_breadcrumb_single_link' ,'remove_shop', 10 ,2);
function remove_shop($link_output, $link ) {
    if( $link['text'] == 'Obchod' ) {
        $link_output = "";
    }
    return $link_output;
}


add_filter( 'woocommerce_get_price_html', 'wpa83367_price_html', 100, 2 );
function wpa83367_price_html( $price, $product ){
    $price = str_replace( '<ins>', ' <span class="price-before">', $price );
    $price = str_replace( '</del>', ' </span>', $price );
    $price = str_replace( '<del', ' <span class="price-after"', $price );
    return $price;
}

/* UPWORK - THIS IS PLACE YOU SHOULD CARE ABOUT */
/* UPWORK - THIS IS PLACE YOU SHOULD CARE ABOUT */
/* UPWORK - THIS IS PLACE YOU SHOULD CARE ABOUT */

// Utility function to get the parent variable product IDs for a any term of a taxonomy
function get_variation_parent_ids_from_term( $term, $taxonomy, $type ){
    global $wpdb;

    return $wpdb->get_col( "
        SELECT DISTINCT p.ID
        FROM {$wpdb->prefix}posts as p
        INNER JOIN {$wpdb->prefix}posts as p2 ON p2.post_parent = p.ID
        INNER JOIN {$wpdb->prefix}term_relationships as tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->prefix}terms as t ON tt.term_id = t.term_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND p2.post_status = 'publish'
        AND tt.taxonomy = '$taxonomy'
        AND t.$type = '$term'
    " );
}

function get_products_by_attributes($category, $is_in_stock = true, $posts_per_page = 10, $page = 1, $variant = '', $color = '', $capacity = ''){
	
	$prod_ids = get_variation_parent_ids_from_term( $category, 'product_cat', 'slug' );
	
	if (empty($prod_ids)) return [];
	
	$tax_query = ['relation'    => 'AND'];
	if ($color != '') {
		$tax_query[] = array(
			'taxonomy'        => 'pa_barva',
			'field'           => 'slug',
			'terms'           =>  $color,
			'operator'        => 'IN',		
		);
	}
	if ($capacity != '') {
		$tax_query[] = array(
			'taxonomy'        => 'pa_kapacita',
			'field'           => 'slug',
			'terms'           =>  $capacity,
			'operator'        => 'IN',		
		);
	}	
	
	
	$products = get_posts( array(    /* Timber::get_posts() is same as new WP_Query() */
		'post_type'       => 'product',
		'post_status'     => 'publish',
		'posts_per_page'  => -1,
		'post__in'	=>	$prod_ids,			
		'tax_query'      => 	$tax_query			
	) );
	
	$product_ids = [];
	foreach ($products as $product) {
		$product_ids[] = $product->ID;
	}
	
	if (empty($product_ids)) return [];
	
	$meta_query = [];
	if ($variant != '') {
		$meta_query = ['relation'    => 'AND'];
		$meta_query[] = array(
			'key'     => 'attribute_pa_varianta', // Product variation attribute
			'value'   => $variant, // Term slugs only
			'compare' => 'IN',			
		);
	}
	
	if ($is_in_stock) {
		$meta_query[] =	array(
				'key' => '_stock_status',
				'value' => 'instock'
			);
		$meta_query[] =	array(
				'key' => '_backorders',
				'value' => 'no'
			);
	}
	
	$offset = ($posts_per_page < 0 ? 0 : ($page - 1)* $posts_per_page);
	
	$args = array(    /* Timber::get_posts() is same as new WP_Query() */
		'post_type'       => 'product_variation',		
		'post_status'     => 'publish',
		'posts_per_page'  => $posts_per_page,
		'offset'		  => $offset,
		'post_parent__in' => $product_ids,
		'meta_query'      => $meta_query,
	);
	
	$post_variations = get_posts( $args );	
	
	$product_variations = [];
	foreach($post_variations as $variant) {
		$product_variations[] = new WC_Product_Variation( $variant->ID );
	}
	
	return $product_variations;
}


add_action('wp', function(){
	$products = get_products_by_attributes('iphone-8', true, -1, 1, '', '', ''); //varianta-a
	//$products = get_products_by_attributes('iphone', true, -1, 1, '', '', '');
	
	bdump($products);

    foreach ($products as $product) {
        echo $product->get_formatted_name() . '<br>';
    }
		
});


/* UPWORK - SECTION BELOW DOES NOT INTEREST YOU - STOP EDITING HERE -*/

/* UPWORK - SECTION BELOW DOES NOT INTEREST YOU - STOP EDITING HERE -*/

/* UPWORK - SECTION BELOW DOES NOT INTEREST YOU - STOP EDITING HERE -*/

/* UPWORK - SECTION BELOW DOES NOT INTEREST YOU - STOP EDITING HERE -*/



// Change add to cart text on product archives page
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives' );  
function woocommerce_add_to_cart_button_text_archives() {
    return __( 'Koupit', 'woocommerce' );
}

function ajax_update_mini_cart() {

    $updated_count = WC()->cart->get_cart_contents_count() - 1;

    echo $updated_count;

    wp_die();
}

add_action( 'wp_ajax_ajax_update_mini_cart', 'ajax_update_mini_cart' ); 
add_action( 'wp_ajax_nopriv_ajax_update_mini_cart', 'ajax_update_mini_cart' ); 

function ajax_sort_products() {

    $products = $_POST['products'];
    $type = $_POST['type'];

 
    for ($x = 0; $x < count($products); $x++)
    {
        $products[$x] = get_post($products[$x]);
    }   

    for ($i = 0; $i < count($products); $i++)
    {
        for ($j = 0; $j < count($products) - $i - 1; $j++)
        {
            $product_object_current = wc_get_product($products[$j]->ID);
            $price_current = $product_object_current->get_price(); 

            $product_object_next = wc_get_product($products[$j + 1]->ID);
            $price_next = $product_object_next->get_price(); 

            if ($type == 'filter-cheap') {
                if ($price_current > $price_next)
                {
                    $t = $products[$j];
                    $products[$j] = $products[$j+1];
                    $products[$j+1] = $t;
                }
            }
            elseif ($type == 'filter-expensive') {
                if ($price_current < $price_next)
                {
                    $t = $products[$j];
                    $products[$j] = $products[$j+1];
                    $products[$j+1] = $t;
                }                
            }

        }
    }

    foreach ($products as $product) {
        Timber::render( 'components/productCard.twig', array( 'ID' => $product->ID, 'items' => 3, ));
    }

    wp_die();
}

add_action( 'wp_ajax_ajax_sort_products', 'ajax_sort_products' ); 
add_action( 'wp_ajax_nopriv_ajax_sort_products', 'ajax_sort_products' ); 


function ajax_add_additional_products() {

    if ( $_POST['main_product_id'] )
        WC()->cart->add_to_cart( $_POST['main_product_id']);

    if ( $_POST['additional_products_ids'] ) {
        foreach ($_POST['additional_products_ids'] as $product_id) {
            WC()->cart->add_to_cart( $product_id );
        }
    }

    echo wc_get_cart_url();

    wp_die();
}

add_action( 'wp_ajax_ajax_add_additional_products', 'ajax_add_additional_products' ); 
add_action( 'wp_ajax_nopriv_ajax_add_additional_products', 'ajax_add_additional_products' ); 


function ajax_product_filter() {

        if ( $_POST['term_id'] != -1) {
            if ($_POST['variations'] && $_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['variations'] && $_POST['capacity'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['variations'] && !$_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['variations'] && $_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['capacity'] && !$_POST['variations'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['capacity'] && $_POST['variations'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['capacity'] && !$_POST['variations'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            else {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $_POST['term_id'],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
        }
        else {
            if ($_POST['variations'] && $_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['variations'] && $_POST['capacity'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['variations'] && !$_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['variations'] && $_POST['capacity'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif ($_POST['capacity'] && !$_POST['variations'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_kapacita',
                                'field' => 'slug',
                                'terms'	=>  $_POST['capacity'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['capacity'] && $_POST['variations'] && !$_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_varianta',
                                'field' => 'slug',
                                'terms'	=>  $_POST['variations'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            elseif (!$_POST['capacity'] && !$_POST['variations'] && $_POST['color']) {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_barva',
                                'field' => 'slug',
                                'terms'	=>  $_POST['color'],
                                'operator'	=> 'IN',
                            ],
                        ],
                    ],
                    'orderby' => 'ID',
                ]);
            }
            else {
                $data = get_posts([
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'orderby' => 'ID',
                ]);
            }
        }

        foreach ($data as $key => $product) {
			$product_object = wc_get_product($product->ID);
            $product_capacity = $product_object->get_attribute('pa_kapacita'); //vezme si atribut kapacity z produktu
            $product_color = $product_object->get_attribute('pa_barva'); //vezme si atribut barvy z produktu

			if ($product_object->get_type() == 'variable') {
				
				$variations = $product_object->get_available_variations();
				$variations_ids = wp_list_pluck( $variations, 'variation_id' );

				foreach ($variations_ids as $index => $variation_id) { //projede všechny varianty z variable produktu

                    $product_variation = wc_get_product($variation_id);

                    if ($product_variation->get_stock_quantity() > 0 && $product_variation->get_stock_status() == 'instock') {


                        $variation = $product_variation->get_attributes()['pa_varianta']; //vezme si stav ke každé variantě produktu

                        if ($_POST['variations'] && $_POST['capacity'] && $_POST['color']) { //zaškrtnuta barva, varianta, kapacita
                            foreach ($_POST['variations'] as $variation_post) {
    
                                $variation_bool = false;
    
                                if ($variation_post == $variation) { //pokud se stav varianty rovná variantě z checkboxu
                                    $variation_bool = true;
                                }
                            }
    
                            foreach ($_POST['capacity'] as $capacity_post) {     
    
                                $capacity_bool = false;
    
                                if ($capacity_post == $product_capacity) { //pokud se kapacita varianty rovná kapacitě z checkboxu
                                    $capacity_bool = true;
                                }
                            }
    
    
                            foreach ($_POST['color'] as $color_post) {     
    
                                $color_bool = false;
    
                                if ($color_post == $product_color) { //pokud se barva varianty rovná barvě z checkboxu
                                    $color_bool = true;
                                }
                            }
    
                            if ($variation_bool && $capacity_bool && $color_bool) {
                                array_push($data, get_post($variation_id)); //přidá na konec pole variantu
                            }
                        }
                        elseif ($_POST['variations'] && $_POST['capacity'] && !$_POST['color']) { //zaškrtnuta varianta a kapacita
                            foreach ($_POST['variations'] as $variation_post) {
    
                                $variation_bool = false;
    
                                if ($variation_post == $variation) { //pokud se stav varianty rovná variantě z checkboxu
                                    $variation_bool = true;
                                }
                            }
    
                            foreach ($_POST['capacity'] as $capacity_post) {     
    
                                $capacity_bool = false;
    
                                if ($capacity_post == $product_capacity) { //pokud se kapacita varianty rovná kapacitě z checkboxu
                                    $capacity_bool = true;
                                }
                            }
    
    
                            if ($variation_bool && $capacity_bool) {
                                array_push($data, get_post($variation_id)); //přidá na konec pole variantu
                            }
                        }
                        elseif ($_POST['variations'] && !$_POST['capacity'] && $_POST['color']) { //zaškrtnuta varianta a barva
                            foreach ($_POST['variations'] as $variation_post) {
    
                                $variation_bool = false;
    
                                if ($variation_post == $variation) { //pokud se stav varianty rovná variantě z checkboxu
                                    $variation_bool = true;
                                }
                            }
    
                            foreach ($_POST['color'] as $color_post) {     
    
                                $color_bool = false;
    
                                if ($color_post == $product_color) { //pokud se barva varianty rovná barvě z checkboxu
                                    $color_bool = true;
                                }
                            }
    
    
                            if ($variation_bool && $color_bool) {
                                array_push($data, get_post($variation_id)); //přidá na konec pole variantu
                            }
                        }
                        elseif (!$_POST['variations'] && $_POST['capacity'] && $_POST['color']) { //zaškrtnuta kapacita a barva
    
                            foreach ($_POST['capacity'] as $capacity_post) {     
    
                                $capacity_bool = false;
    
                                if ($capacity_post == $product_capacity) { //pokud se kapacita varianty rovná kapacitě z checkboxu
                                    $capacity_bool = true;
                                }
                            }
    
    
                            foreach ($_POST['color'] as $color_post) {     
    
                                $color_bool = false;
    
                                if ($color_post == $product_color) { //pokud se barva varianty rovná barvě z checkboxu
                                    $color_bool = true;
                                }
                            }
    
                            if ($capacity_bool && $color_bool) {
                                array_push($data, get_post($variation_id)); //přidá na konec pole variantu
                            }
                        }
                        elseif ($_POST['capacity'] && !$_POST['variations'] && !$_POST['color']) { //zaškrtnuta jen kapacita
                            foreach ($_POST['capacity'] as $capacity) {
                                
                                if ($capacity == $product_capacity) {
                                    array_push($data, get_post($variation_id));
                                }
                            }
                        }
                        elseif (!$_POST['capacity'] && $_POST['variations'] && !$_POST['color']) { //zaškrtnuta jen varianta (stav)
    
                            foreach ($_POST['variations'] as $variation_post) {
    
                                if ($variation_post == $variation) { //pokud se stav varianty rovná variantě z checkboxu
                                    array_push($data, get_post($variation_id));
                                }
                            }
                        }
                        elseif (!$_POST['capacity'] && !$_POST['variations'] && $_POST['color']) { //zaškrtnuta jen barva
                
                            foreach ($_POST['color'] as $color) {
                                
                                if ($color == $product_color) {
                                    array_push($data, get_post($variation_id));
                                }
                            }
                        }
                        else {
                            array_push($data, get_post($variation_id));
                        }
                    }
				}
                unset($data[$key]); //smaže z pole produktů hlavní produkt, ze kterého se vygenerovaly varianty - smaže stylem, že odstraní z indexu, ale nepřeindexuje
			}
            else { //pro simple produkty
				if ($product_object->get_stock_quantity() == 0 && $product_object->get_stock_status() != 'instock') //je produkt skladem?
					unset($data[$key]); //odstranění produktu který není skladem
			}
            // když nemá produkt varianty ale je variable tak asi se smaže přes array_splice - ověřit, kdyžtak oifovat a zjistit get avaialbe variations
		}

        foreach (array_values($data) as $key => $product) { // přeindexuje pole před procházením
            timber_set_product($product);
            $productToShow = 12;
                        
            if ($key < $productToShow) {
                Timber::render( 'components/productCard.twig', array( 'ID' => $product->ID, 'items' => 3, ));
            }
            else {
                Timber::render( 'components/productCard.twig', array( 'ID' => $product->ID, 'items' => 3, 'hidden' => true ));
            }
        }


    wp_die();
}

add_action( 'wp_ajax_ajax_product_filter', 'ajax_product_filter' ); 
add_action( 'wp_ajax_nopriv_ajax_product_filter', 'ajax_product_filter' ); 











