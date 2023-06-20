<?php

/**
 * Utility functions for the plugin
 *
 * @author Geek Web Solution
 */
class wssmgk_schedule_stock_manager
{
	static function wssmgk_shedule_setup()
	{
		add_action('my_hourly_event', array('wssmgk_schedule_stock_manager', 'do_this_hourly'));
	}
	static function do_this_hourly($post_id)
	{
		$wssmgk_stock = get_post_meta($post_id, 'wssmgk_stock', true);
		$main_stock = get_post_meta($post_id, '_stock', true);
		if (empty($wssmgk_stock)) $wssmgk_stock = 0;
		if (empty($main_stock)) $main_stock = 0;

		$final_stock = $main_stock + $wssmgk_stock;

		if(empty($final_stock) || $final_stock < 0) {
			update_post_meta($post_id, "_stock_status", 'outofstock');
		}elseif($final_stock > 0) {
			update_post_meta($post_id, "_stock_status", 'instock');
		}
		update_post_meta($post_id, "_stock", $final_stock);

		// update variations stock status to instock
		$product = wc_get_product($post_id);
		if ($product->is_type('variable') && $product->get_manage_stock()) {
			$wssmgk_schedule_mode = get_post_meta($post_id, 'wssmgk_schedule_mode', true);
			$schedule_qty_type = get_post_meta($post_id, 'wssmgk_schedule_qty_type', true);

			if (!empty($wssmgk_schedule_mode) && $final_stock > 0) {
				$variations = $product->get_available_variations();
				$variations_id = wp_list_pluck($variations, 'variation_id');

				if (isset($variations_id) && !empty($variations_id)) {
					foreach ($variations_id as $key => $variation_id) {
						$variation_obj = new WC_Product_variation($variation_id);
						$stock = $variation_obj->get_stock_quantity();
						if ($stock > 0) update_post_meta($variation_id, '_stock_status', 'instock');
					}
				}
			}
		}
	}
	static function wssmgk_add_intervals($schedules)
	{
		// Add a Every minute interval.
		$schedules['wssmgk_every_minute'] = array(
			'interval' => 60,
			'display'  => __('Every Minute'),
		);
		// Add a Every Hourly interval.
		$schedules['wssmgk_hourly'] = array(
			'interval' => 3600,
			'display'  => __('Hourly'),
		);
		// Add a Twice Daily interval.
		$schedules['wssmgk_twicedaily'] = array(
			'interval' => 43200,
			'display'  => __('Twice Daily (Every 12 Hour)'),
		);
		// Add a Every Day interval.
		$schedules['wssmgk_every_day'] = array(
			'interval' => 86400,
			'display'  => __('Daily (Every 24 Hour)'),
		);

		// Add a Every Two Days interval.
		$schedules['wssmgk_every_two_days'] = array(
			'interval' => 172800,
			'display'  => __('Every Two Days (Every 48 Hour)'),
		);
		// Add a weekly interval.
		$schedules['wssmgk_weekly'] = array(
			'interval' => 604800,
			'display'  => __('Once Weekly (Every 7 Days)'),
		);

		// Add a  Twice Monthly interval.
		$schedules['wssmgk_twicemonthly'] = array(
			'interval' => 1296000,
			'display'  => __('Twice Monthly (Every 15 Days)'),
		);

		// Add a Monthly interval.
		$schedules['wssmgk_monthly'] = array(
			'interval' => 2592000,
			'display'  => __('Monthly (Every 30 Days)'),
		);

		// Add a Yearly interval.
		$schedules['wssmgk_yearly'] = array(
			'interval' => 31536000,
			'display'  => __('Yearly (Every 365 Days)'),
		);
		return $schedules;
	}
	static function get_schedule_type()
	{
		$options[''] = __('Select a value', 'woocommerce'); // default value
		$recurrence = wp_get_schedules();
		foreach ($recurrence as $key => $type) {
			if (strpos($key, 'wssmgk') !== false) {
				$options[$key] = $type['display'];
			}
		}
		$options['wssmgk_custom_date'] = __('Custom Date (Custom Date Time)', 'woocommerce');
		return $options;
	}
	static function get_schedule_time()
	{
		$options = array();
		$options['wssmgk_every_minute'] = 60;
		$options['wssmgk_hourly'] = 3600;
		$options['wssmgk_twicedaily'] = 43200;
		$options['wssmgk_every_day'] = 86400;
		$options['wssmgk_every_two_days'] = 172800;
		$options['wssmgk_weekly'] = 604800;
		$options['wssmgk_twicemonthly'] = 1296000;
		$options['wssmgk_monthly'] = 2592000;
		$options['wssmgk_yearly'] = 31536000;
		return $options;
	}
}
