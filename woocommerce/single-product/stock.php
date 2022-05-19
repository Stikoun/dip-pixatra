<?php
/**
 * Single Product stock.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/stock.php.
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
	exit;
}

use App\Controllers\Templates\SingleProductController;
$current_product = $product;
?>

<div class="flex justify-between md:px-4">
	<div class="basis-1/2 shrink-0">
		<div class="flex items-center">
	<?php 
	if ($product->get_availability()['class'] == 'in-stock') {
		echo '<svg class="inline-block h-6 w-6 mr-3 fill-green" style="" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
		<g>
			<g>
				<path d="M504.527,67.909c-8.611-6.92-21.2-5.547-28.118,3.063L210.291,402.169c-3.612,3.863-8.494,6.101-13.797,6.314
					c-5.459,0.22-10.629-1.73-14.523-5.431L33.839,261.061c-7.975-7.643-20.634-7.374-28.278,0.599
					c-7.643,7.974-7.375,20.634,0.599,28.278l148.191,142.048c11.26,10.703,25.83,16.515,41.268,16.515
					c0.825,0,1.655-0.017,2.484-0.051c16.352-0.657,31.371-7.734,42.288-19.926c0.237-0.265,0.467-0.537,0.691-0.814L507.59,96.027
					C514.508,87.416,513.137,74.828,504.527,67.909z"></path>
			</g>
		</g>
		</svg>';
		if ($product->get_stock_quantity() > 5) {
			echo '<span class="text-20 text-green font-bold">Skladem více než 5 ks</span>';
		}
		else if ($product->get_stock_quantity() <= 5){
			echo '<span class="text-20 text-green font-bold">Skladem už jen '.$product->get_stock_quantity().'</span>';
		}
		else {
			echo '<span class="text-20 text-green font-bold">Skladem</span>';		
		}
	}	
	else {
		echo '<svg class="inline-block h-6 w-6 mr-3 fill-red" style="" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 490 490" xml:space="preserve">
		<polygon points="456.851,0 245,212.564 33.149,0 0.708,32.337 212.669,245.004 0.708,457.678 33.149,490 245,277.443 456.851,490 
			489.292,457.678 277.331,245.004 489.292,32.337 "></polygon></svg>';
		echo '<span class="text-20 text-red font-bold">Není skladem</span>';	
	}	
	?>	
		</div>
		<div class="mt-2 text-12">
			<?php echo SingleProductController::getCustomAvailability($current_product); ?>
        </div>	




