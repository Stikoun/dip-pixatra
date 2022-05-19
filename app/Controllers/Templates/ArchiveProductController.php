<?php

namespace App\Controllers\Templates;

use Timber\Timber;
use WC_Product_Query;

class ArchiveProductController
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

		if (!isset(get_queried_object()->term_id)) { //pro shop page, když je term null
			$products = get_posts([
				'post_type' => 'product',
				'posts_per_page' => -1,
			]);

			$context['subcategories'] = $this->appendDataToTerms(
				get_terms(
					array(
						'taxonomy' => 'product_cat',
						'hide_empty' => true,
						'parent' => 0
					)
				)
			);
		}
		else {
			$products = get_products_by_attributes(get_queried_object()->term_id, true, -1, 1, '', '', ''); //varianta-a
			
			$context['term_id'] = get_queried_object()->term_id;
			$context['category'] = get_term( get_queried_object()->term_id, 'product_cat' );

			$subcategories = $this->appendDataToTerms(get_terms(array('taxonomy' => 'product_cat', 'parent' => get_queried_object()->term_id, 'hide_empty' => true)));	
			$subcategories = $this->getVisibleSubCategories($subcategories);
			$context['subcategories'] = $subcategories;
		}

		$context['options'] = get_fields('options');

		// $transformed_products = $this->transformProducts($products_query, true);
		$context['products'] = $products;
		bdump($products);
		// $context['products'] = $transformed_products;
		// $context['products'] = $this->sortProducts($transformed_products, 'filter-cheap');

		// $context['attribute_varianta'] = $this->getProductAttributes('pa_varianta', $context['products']);
		// $context['attribute_kapacita'] = $this->getProductAttributes('pa_kapacita', $context['products']);
		// $context['attribute_barva'] = $this->getProductAttributes('pa_barva', $context['products']);


		$context['best_sellers'] = get_field('best_sellers', get_queried_object());
	

		if ($context['products']) {
			Timber::render('templates/woo/archive-product.twig', $context);
		}
		else {
			Timber::render('templates/woo/archive-product-empty.twig', $context);
		}

	}

	public function getProductAttributes($attribute, $products) {
		$attributes = [];

		foreach ($products as $key => $product) {
			$product = wc_get_product( $product->ID );

			if ($product->get_type() == 'simple') {  //pro simple produkt

				if ($product->get_attributes() && isset($product->get_attributes()[$attribute]) && isset($product->get_attributes()[$attribute]->get_slugs()[0])) {
					$attributes[$key]['slug'] = $product->get_attributes()[$attribute]->get_slugs()[0];
					$attributes[$key]['name'] = $product->get_attribute($attribute);
				}
			}
			else { //pro variable produkt
				if ($attribute == 'pa_kapacita' || $attribute == 'pa_barva') { // pro atributy, které nejsou využívány pro varianty - používá se jiný formát ID
					$product_parent = wc_get_product($product->id);
					
					if ($product_parent->get_attributes() && isset($product_parent->get_attributes()[$attribute])) {
						$attributes[$key]['slug'] = $product_parent->get_attribute($attribute);
						$attributes[$key]['name'] = $product_parent->get_attribute($attribute);
					}
				}
				else { // pro atributy, které jsou zároveň použity jako varianty
					if ($product->get_attributes() && isset($product->get_attributes()[$attribute])) {
						$attributes[$key]['slug'] = $product->get_attributes()[$attribute];	
						$attributes[$key]['name'] = $product->get_attribute($attribute);		
					}	
				}
			}
		}

		$attributes = array_unique($attributes, SORT_REGULAR);

		foreach ($attributes as $key => $attribute) {
			if ($attribute['name'] == 'A+') {
				$attributes[0] = $attribute;

				if ($key != 0 && $key != 1 && $key != 2)
					unset($attributes[$key]);
			}
			if ($attribute['name'] == 'A') {
				$attributes[1] = $attribute;

				if ($key != 0 && $key != 1 && $key != 2)
					unset($attributes[$key]);	
			}
			if ($attribute['name'] == 'B+') {
				$attributes[2] = $attribute;

				if ($key != 0 && $key != 1 && $key != 2)
					unset($attributes[$key]);
			}
		}

		ksort($attributes);


		return $attributes;
	}


	public function appendDataToTerms ($terms) {
		foreach ($terms as $term) {
			$thumbnail_id = get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true );
			$image = wp_get_attachment_url( $thumbnail_id );
			$link = get_term_link($term);

			$term->thumbnail = $image;
			$term->link = $link;
		}	
		return $terms;
	}

	public static function transformProducts ($products, $limit) {
		
		foreach ($products as $key => $product) { //projedu všechny produkty

			$product_object= wc_get_product($product->ID);

			if ($product_object->get_type() == 'variable') { //pro variable produkt

				$variations = $product_object->get_available_variations(); //vytáhnu si všechny jeho dostupné varianty

				if (!empty($variations) || (!is_null($variations)) ) { //pokud má vyplněné nějaké varianty
					$variations_ids = wp_list_pluck( $variations, 'variation_id' ); //idčka variant

					foreach ($variations_ids as $index => $variation_id) { //projedu idčka variant
						$variation_product_object = wc_get_product($variation_id);
						$variation_object = get_post($variation_id);

						if ($variation_product_object->get_stock_quantity() > 0 && $variation_product_object->get_stock_status() == 'instock') //je varianta skladem?
							array_push($products, $variation_object); //zařadím na konec pole objekt produktu (varianty)
					}

					unset($products[$key]); //odstraním z pole hlavní produkt, ze kterého jsem si vytáhl varianty a nepřeindexovávám pole

				}
				else {
					unset($products[$key]);  //pro variable produkt, který nemá varianty - odstraním ho
				}
			}
			else { //pro simple produkty
				if ($product_object->get_stock_quantity() == 0 && $product_object->get_stock_status() != 'instock') //je produkt skladem?
					unset($products[$key]); //odstranění produktu který není skladem
			}
		}
		
		return array_values($products); //přeindexuju pole			

	}

	private function sortProducts($products, $type) {
		
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

		return $products;
	}

	private function getVisibleSubCategories($subcategories) {
		foreach ($subcategories as $key => $category) {
			$is_visible = get_field('is_visible', $category);
			
			if ($is_visible == false && !is_null($is_visible)) {
				unset($subcategories[$key]);
			}
		}

		return $subcategories;
	}
}
