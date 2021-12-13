<?php if (!defined('ABSPATH') || !is_plugin_active( 'woocommerce/woocommerce.php' )) exit;

/*
 * Determines if the cart is eligible for free products and returns the quantity. Returns 0 if not eligible.
 */
function get_number_of_eligible_free_products($cart)
{
	$cart_subtotal = 0;
	$free_product_id = defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID') ? WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID : 0;
	$free_product_threshold_price = defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRICE_TRIGGER') ? WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRICE_TRIGGER : 0;
	$free_product_trigger_product_id = defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRODUCT_TRIGGER_PRODUCT_ID') ? WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRODUCT_TRIGGER_PRODUCT_ID : 0;

	if ($free_product_id > 0) {
		foreach ($cart as $cart_item_key => $cart_item) {

			// Skip products that could be free
			if ($cart_item['product_id'] == WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID) {
				continue;
			}

			// Calculate items subtotal and add discounted line total with taxes
			$cart_subtotal += $cart_item['quantity'] * wc_get_price_including_tax($cart_item['data']);

			// Check if trigger product is in cart and return its quantity
			if($free_product_trigger_product_id > 0 && $cart_item['product_id'] == $free_product_trigger_product_id) {
				return $cart_item['quantity'];
			}
		}
	}
	// Check if cart subtotal is greater than threshold price to return 1 instead of 0, since trigger product was not found
	return $free_product_threshold_price > 0 && $cart_subtotal >= $free_product_threshold_price ? 1 : 0;
}

/*
 * Give free product on certain conditions.
 */
if (
	defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID') && WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID &&
	(
		(defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRICE_TRIGGER') && WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRICE_TRIGGER) ||
		(defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRODUCT_TRIGGER_PRODUCT_ID') && WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRODUCT_TRIGGER_PRODUCT_ID)
	)
) {

	add_action('woocommerce_before_calculate_totals', function ($cart) {

		if (is_admin() && !defined('DOING_AJAX')) {
			return;
		}

		$number_of_eligible_free_products = get_number_of_eligible_free_products($cart->get_cart());

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if ($cart_item['product_id'] == WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID) {
				if ($number_of_eligible_free_products > 0) { // If is eligible for free item, make sure to end up with $number_of_eligible_free_products free items
					$cart_item['data']->set_price(0);
					if ($cart_item['quantity'] != $number_of_eligible_free_products) {
						$cart->set_quantity($cart_item_key, $number_of_eligible_free_products);
					}
				} elseif ($cart_item['data']->get_price() == 0) { // If not eligible for free item, but still having one or more free items, make sure to end up with 0 free items
					$cart->remove_cart_item(WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID);
				}
				return;
			}
		}

		if ($number_of_eligible_free_products > 0) { // Add item if eligible but not in cart yet.
			$cart->add_to_cart(WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID, 1);
		}
	}, 0);

	// display free product price to zero on minicart
	add_filter('woocommerce_cart_item_price', function ($price_html, $cart_item, $cart_item_key) {

		global $woocommerce;
		if (defined('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID') && $cart_item['product_id'] == WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID && get_number_of_eligible_free_products($woocommerce->cart->get_cart())) {
			$price_html = wc_price(0);
		}
		return $price_html;
	}, 10, 3);

}
