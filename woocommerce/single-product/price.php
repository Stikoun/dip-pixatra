<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

?>

<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>"><?php echo $product->get_price_html(); ?></p>

<? echo wc_get_stock_html( $product ); // WPCS: XSS ok. ?>
    <div>
        <span class="block text-40 text-red font-bold leading-8"><?php echo wc_price($product->sale_price); ?></span>
        <div class="text-right">
           <!-- <span class="text-18 font-bold text-red text-right"><?php // echo (1 - ($product->sale_price / $product->regular_price)) * 100; ?></span> -->
            <span class="text-18 text-gray font-bold line-through text-right"><?php echo wc_price($product->regular_price); ?></span>
        </div>
    </div>
</div>
