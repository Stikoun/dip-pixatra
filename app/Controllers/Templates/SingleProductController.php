<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class SingleProductController
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
		$context['options'] = get_fields('options');	
		$context['acf'] = get_fields();
		$context['product'] = wc_get_product(Timber::get_post()->ID);
		$context['availability'] = $this->getCustomAvailability($context['product']);
		$context['accessories'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'accessories', false);
		$context['attributes'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'product_attributes', true);
		$context['service'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'service', true);
		$context['additional_products'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'additional_products', true);
		$context['additional_gallery'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'product_gallery', false);
		$context['description'] = $this->getProductCategoryACF(Timber::get_post()->ID, 'product_description', true);

		if ($context['product']->get_type() == 'simple') {			
			Timber::render('templates/woo/single-product/single-product-simple.twig', $context);
		}
		else {	
			$context['variations'] = $this->getProductVariations();			
			Timber::render('templates/woo/single-product/single-product-variable.twig', $context);
		}
		

	}

	// private function getAllAttributes() {
	// 	$product = wc_get_product(Timber::get_post()->ID);
	// 	$attributes = $product->get_attributes();

	// 	$output = array();


	// 	foreach ($attributes as $attribute) {	
	// 		$attribute_label = wc_attribute_label($attribute['name']);

	// 		$output[$attribute_label] = $product->get_attribute($attribute['name']);
	// 	}

	// 	return $output;
	// }

	private function getProductCategoryACF($product, $field, $include_child) {

		$terms = get_the_terms( $product, 'product_cat' );

		$category_child = [];
		$category_parent = [];

		if ($include_child) {
			foreach ($terms as $key => $category) {
				if ($category->parent == 0) {
					$category_parent[] = $category;
				}
				else {
					$category_child[] = $category;
				}
			}

			if ($category_child) {
				$attrs = get_field($field, $category_child[0]);
			}
			else {
				$attrs = get_field($field, $category_parent[0]);
			}
		}
		else {
			$category_parent;

			foreach ($terms as $key => $category) {
				if ($category->parent == 0) {
					$category_parent = $category;
				}
			}

			$attrs = get_field($field, $category_parent);
		}

		return $attrs;
	}


	private function getProductVariations() {
		$product = wc_get_product(Timber::get_post()->ID);
		$variations = $product->get_available_variations();

		$output = array();

		foreach ($variations as $key_x => $variation) {
			foreach ($variation['attributes'] as $key_y => $value) {
				$output[$value] = $variation['display_price'];
			} 
		}

		return $output;
	}

	public static function getCustomAvailability( $product ) {
		date_default_timezone_set('Europe/Prague');
		$day = date("N");
		$is_regular_day = false;
		$local_shop = '';

		if($day == 1 || $day == 2 || $day == 3 || $day == 4)
			$is_regular_day = true;
		
		if($day == 6 || $day == 7) 
			$local_shop = 'v PONDĚLÍ od 10:00';
		elseif($day == 5 && date("H") > 17) 
			$local_shop = 'v PONDĚLÍ od 10:00';
		elseif((date("H") > 15 && date("i") > 45) || date("H") > 16)
			$local_shop = 'ZÍTRA od 10:00';
		else 
			$local_shop = 'IHNED';

		$icon = dh_svg('question', ['class' => 'fill-gray h-3 w-3 inline-block']);
		$popup = '<div class="hidden absolute shadow-circle w-[150px] bg-white p-3 left-1/2 bottom-full -translate-x-1/2 z-10 text-center group-hover:block">Na pobočce Korunní 1295/55, Praha 2</div>';
		
		
		if ( $product->is_in_stock() && $is_regular_day && date("H") < 14) {
			$availability = __('<span class="relative group mb-2 inline-block">Osobní odběr - <u><strong>'.$local_shop.'</u></strong>
			'.$popup . $icon .'</span><br><span>Odešleme ještě <u><strong>DNES</u></strong></span>.', 'woocommerce');
		}
		else if ( $product->is_in_stock() && $is_regular_day && date("H") >= 14) {
			$availability = __('<span class="relative group mb-2 inline-block">Osobní odběr - <u><strong>'.$local_shop.'</u></strong>
			'.$popup . $icon .'</span><br><span>Odešleme <u><strong>ZÍTRA</u></strong></span>', 'woocommerce');
		}
		else if ($product->is_in_stock() && $day == 5 && date("H") < 14 ) {
			$availability = __('<span class="relative group mb-2 inline-block">Osobní odběr - <u><strong>'.$local_shop.'</u></strong>
			'.$popup . $icon .'</span><br><span>Odešleme <u><strong>DNES</u></strong></span>', 'woocommerce');
		}
		else if ($product->is_in_stock()) {
			$availability = __('<span class="relative group mb-2 inline-block">Osobní odběr - <u><strong>'.$local_shop.'</u></strong>
			'.$popup . $icon .'</span><br><span>Odešleme v <u><strong>PONDĚLÍ</u></strong></span>', 'woocommerce');
		} 
		else {
			$availability = "";
		}
		

		return $availability;
	}
}
