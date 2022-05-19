<?php

if ( is_singular( 'product' ) ) {
    new App\Controllers\Templates\SingleProductController();
}
elseif ( is_search() ) {
    new App\Controllers\Templates\SearchController();
}
elseif ( is_product_category() || is_shop() ) {
    new App\Controllers\Templates\ArchiveProductController();
}
elseif ( is_account_page() ) {
    new App\Controllers\Templates\AccountController();
}
elseif ( is_cart() ) {
    new App\Controllers\Templates\CartController();
}
elseif ( is_checkout() ) {
    new App\Controllers\Templates\CheckoutController();
}
elseif ( is_front_page() ) {
    new App\Controllers\Templates\HomeController();
}
else {
    new App\Controllers\Templates\PageController();  
}
