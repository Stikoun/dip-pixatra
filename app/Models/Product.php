<?php

namespace App\Models;

use Timber\Timber;

class Product
{
	public static function getCategoryThumbnail ($term_id) {
        $thumbnail_id = get_woocommerce_term_meta( $term_id, 'thumbnail_id', true );
        $image = wp_get_attachment_url( $thumbnail_id );

        return $image;
	}
}



