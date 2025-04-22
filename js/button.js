(function (Drupal) {
  Drupal.behaviors.commerceWishlistButton = {
    attach(context) {
      const buttons = once('wishlist-button', '.commerce_wishlist_button--wishlist-button', context);

      buttons.forEach((button) => {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          const wrapper = button.closest('.wishlist-button-wrapper');
          const url = button.getAttribute('href');

          button.classList.add('is-loading');

          fetch(url, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'same-origin'
          })
          .then((response) => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then((html) => {
            wrapper.outerHTML = html;
          })
          .catch(() => {
            alert(Drupal.t('An error occurred. Please try again.'));
          });
        });
      });
    }
  };
})(Drupal);

/*
(function ($, Drupal) {
  Drupal.behaviors.commerceWishlistButton = {
    attach: function (context) {
      $('.wishlist-button', context).once('wishlist-button').on('click', function(e) {
        e.preventDefault();
        const $button = $(this);
        
        $.ajax({
          url: $button.attr('href'),
          type: 'POST',
          success: function(response) {
            $button.closest('.wishlist-button-wrapper').replaceWith(response);
          }
        });
      });
    }
  };
})(jQuery, Drupal);
/**/