<?php

namespace App\Controllers\Templates;

use Timber\Timber;


class HomeController
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
        $context['acf'] = get_fields();
		$context['options'] = get_fields('options');
		$context['post'] = Timber::get_post();
		// $context['reviews'] = $this->getGoogleReviews();

		Timber::render('templates/home.twig', $context);
	}

	// private function getGoogleReviews() {
	// 	$args = array(
	// 		'headers' => array(
	// 		  'Content-Type' => 'application/json',
	// 		  'X-Api-Key' => 'YXV0aDB8NjI2YTRiM2QxMDQ1MjAwMDY5MjYwMmQxfDdmYzBhOTRiYTA'
	// 		)
	// 	);

	// 	$url = 'https://api.app.outscraper.com/maps/reviews-v3?query=ChIJrUAewpSTC0cRQSUkNKFHdd8&reviewsLimit=6&async=false&sort=newest&ignoreEmpty=true';

	// 	$response = wp_remote_get($url, $args);
	
	// 	echo "<pre>";
	// 	print_r($response);
	// 	echo "</pre>";

	// 	if(!is_wp_error($response)) {
	// 		$json = json_decode( $response['body'], true );

	// 		return $json['data'][0]['reviews_data'];
	// 	}
	// }
}
