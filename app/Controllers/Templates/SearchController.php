<?php

namespace App\Controllers\Templates;

use Timber\Timber;

class SearchController
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

		global $wp_query;

		$products_query = $wp_query->posts;

		$context['options'] = get_fields('options');
		$context['searchTerm'] = get_search_query();

		$transformed_products = $this->transformProducts($products_query, true);
		$context['products'] = $this->sortProducts($transformed_products, 'filter-cheap');

		if ($context['products']) {
			Timber::render('templates/woo/search-archive.twig', $context);
		}
		else {
			Timber::render('templates/woo/search-archive-empty.twig', $context);
		}

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
}