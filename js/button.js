(function(Drupal) {
	Drupal.behaviors.commerceWishlistButton = {
		attach(context) {
			const buttons = once('wishlist-button', '.commerce_wishlist_button--wishlist-button', context);
			buttons.forEach((button) => {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					const wrapper = button.closest('.commerce-wishlist-button--wrapper');
					const url = button.getAttribute('href');
					if (!wrapper.classList.contains('is-loading')) {
						wrapper.classList.add('is-loading');
						fetch(url, {
							method: 'POST',
							headers: {
								'X-Requested-With': 'XMLHttpRequest',
								'Content-Type': 'application/json',
								'Accept': 'application/json'
							},
							credentials: 'same-origin'
						}).then((response) => {
							if (!response.ok) { throw new Error('Network response was not ok'); }
							return response.json();
						}).then((jsonReponse) => {
							wrapper.classList.remove('is-loading');
							if (jsonReponse && jsonReponse.action == 'add') {
								wrapper.classList.add('add');
								wrapper.classList.remove('remove');
							} else {
								wrapper.classList.remove('add');
								wrapper.classList.add('remove');
							}
						})
							.catch(() => {
								wrapper.classList.remove('is-loading');
								alert(Drupal.t('An error occurred. Please try again.'));
							});
					}
				});
			});
		}
	};
})(Drupal);