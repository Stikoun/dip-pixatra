/**
 * swiperjs
 *
 * @see https://swiperjs.com/
 */
 import Swiper from 'swiper/bundle';
 import 'swiper/css/bundle';

 window.addEventListener('DOMContentLoaded', (event) => {
  const mainSlider = new Swiper('.main-slider', {
    loop: true,
    autoplay: {
      delay: 7000,
      pauseOnMouseEnter: true,  
    },
    navigation: {
        nextEl: '.btn-next-slide',
        prevEl: '.btn-prev-slide',
    },
  });


  const productThumbsSlider = new Swiper('.product-thumbs-slideshow', {
    spaceBetween: 10,
    slidesPerView: 4,
    freeMode: true,
    breakpoints: {
      320: {
        slidesPerView: 3,
        spaceBetween: 10,
      },
      1024: {
        slidesPerView: 4,
        spaceBetween: 10,
      }
    }
  });

  const productSlider = new Swiper('.product-slideshow', {
    loop: true,
    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },
    preloadImages: true,
    lazy: true,
    thumbs: {
        swiper: productThumbsSlider
    }
  });

  const testimonialsSlider = new Swiper('.testimonials-slider', {
    loop: true,
    slidesPerView: 1,
    centeredSlides: true,
    spaceBetween: 20,
    slideToClickedSlide: true,
    autoplay: {
      delay: 7000,
      pauseOnMouseEnter: true,  
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true,
    },
    breakpoints: {
      1024: {
        slidesPerView: 3,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 0,
      }
    }
  });

  const branchSlider = new Swiper('.branch-slider', {
    loop: true,
    slidesPerView: 1,
    centeredSlides: true,
    spaceBetween: 20,
    slideToClickedSlide: true,
    autoplay: {
      delay: 7000,
      pauseOnMouseEnter: true,  
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true,
    },
    breakpoints: {
      1024: {
        slidesPerView: 3,
      },
      768: {
        slidesPerView: 2,
      }
    }
  });
});


jQuery('.mobile-menu').click(function() {

  if (!jQuery(this).hasClass('active')) {
    jQuery(this).addClass('active');
  }
  else {
    jQuery(this).removeClass('active');
  }

})

jQuery(function() {
  jQuery(".remove").click(function(event) {

      jQuery.ajax({
          type: 'POST',
          url: woocommerce_params.ajax_url,
          data: {
              'action'   : 'ajax_update_mini_cart',
          },
          success: function( response ) {
              jQuery('.cart_count').html(response);
          },
      });
  });
})


const tabs_list = document.querySelector('.tabs__list');
const tabs_items = document.querySelectorAll('.tabs__list li');

tabs_items.forEach( function (element) {
    element.addEventListener('click', function (element) {

        tabs_list.scroll({
          left: element.currentTarget.offsetWidth,
          top: 0,
          behavior: 'smooth'
        });
        
        tabs_items.forEach( function (el) {
            el.classList.remove('active');
        })

        const clicked_target = this.getAttribute('data-target');
        this.classList.add('active');

        document.querySelectorAll('.tabs__content > div').forEach( function (target) {
            if (clicked_target == target.getAttribute('id')) {
              if (!target.classList.contains('active')) {
                  target.classList.add('active');
              }
            }
            else {
              target.classList.remove('active');
            }
        })
    })
})

jQuery("#show-more").on('click', function() {
    if (jQuery("#description > div").hasClass('shrinked')) {
        jQuery("#description > div").removeClass('shrinked');  
        jQuery(this).hide();     
    }
    else {
        jQuery("#description > div").addClass('shrinked');
    }
})



jQuery('.woocommerce table.shop_table .cart_item').each(function() {
  let span = jQuery(this).find('.product-price > span').clone();
  jQuery(this).find('.product-name').append('<div class="md:hidden"></div>');
  jQuery(this).find('.product-name > div').append(span);
})

jQuery('.dgwt-wcas-search-form').on('submit', function(event) {
  event.preventDefault();
})









