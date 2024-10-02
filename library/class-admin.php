<?php
/**
 * Utility functions for the plugin
 *
 * @author Geek Code Lab
 */
class wssmgk_auto_stock_manager
{

	static function wssmgk_setup()
	{
		add_filter('cron_schedules', array('wssmgk_schedule_stock_manager', 'wssmgk_add_intervals'));
		add_action('woocommerce_product_options_stock_status', array('wssmgk_auto_stock_manager', 'wssmgk_main_product_options'));
		add_action('woocommerce_product_after_variable_attributes', array('wssmgk_auto_stock_manager', 'wssmgk_variation_product_options'), 10, 3);
		add_action('woocommerce_process_product_meta', array('wssmgk_auto_stock_manager', 'wssmgk_save_main_product_options'));
		// Save Variation Settings
		add_action('woocommerce_save_product_variation', array('wssmgk_auto_stock_manager', 'wssmgk_save_variation_product_options'), 10, 2);
		if (is_admin()) {
			add_action('admin_enqueue_scripts', array('wssmgk_auto_stock_manager', 'wssmgk_enqueue_admin_scripts'));
		}
	}
	static function wssmgk_main_product_options()
	{
		$wssmgk_ProPlugin_schedule = get_post_meta(get_the_ID(), 'wssmgkp_schedule', true);
		if ($wssmgk_ProPlugin_schedule == 'wssmgkp_every_month_specific_date_time') {
			update_post_meta(get_the_ID(), 'wssmgkp_schedule', '');
			update_post_meta(get_the_ID(), 'wssmgk_schedule_mode', 'no');
			update_post_meta(get_the_ID(), 'wssmgk_schedule', '');
			update_post_meta(get_the_ID(), 'wssmgk_stock', '');
			wp_clear_scheduled_hook('wssmgkp_shedule_event', array(get_the_ID()));
		}

		$recurrence_type = wssmgk_schedule_stock_manager::get_schedule_type();
		$schedule_mode = get_post_meta(get_the_ID(), 'wssmgk_schedule_mode', true);
		$wssmgk_schedule = get_post_meta(get_the_ID(), 'wssmgk_schedule', true);

		$custom_attr = array(
			"min" => 0,
			"oninput" => "this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null",
		);

		if ($schedule_mode == 'yes') {
			$display = "style='display:block'";
			$custom_attr['required'] = "required";
		} else {
			$display = "style='display:none'";
			
		}
		//Auto Manage Stock
		echo '<div class="stock_fields show_if_simple wssmgk_variation_opt">';
		woocommerce_wp_checkbox(array(
			'id' => 'wssmgk_schedule_mode',
			'class' => 'add-required',
			'value' => get_post_meta(get_the_ID(), 'wssmgk_schedule_mode', true),
			'label' => __('Schedule Stock Manage', 'woocommerce-schedule-stock-manager'),
			'description' => __('Enable to auto add stock quantity as per you choose Schedule Type', 'woocommerce-schedule-stock-manager')
		));
		echo '<div class="wssmgk_advance_option" ' . $display . '>';
		// Recurrence Type
		woocommerce_wp_select(
			array(
				'id' => 'wssmgk_schedule',
				'class' => 'wssmgk_variation_schedule',
				'label' => __('Schedule Type', 'woocommerce-schedule-stock-manager'),
				'desc_tip' => 'true',
				'description' => __('This will be executed on a specific interval', 'woocommerce-schedule-stock-manager'),
				'options' => $recurrence_type,
				'value' => get_post_meta(get_the_ID(), 'wssmgk_schedule', true)
			)
		);
		// Stock quantity
		woocommerce_wp_text_input(
			array(
				'id' => 'wssmgk_stock',
				'label' => __('Stock quantity', 'woocommerce-schedule-stock-manager'),
				'desc_tip' => 'true',
				'class' => 'wssmgk_stock_check',
				'type' => 'number',
				'description' => __('This Stock Quanity will be added on main stock as per you chosen Schedule Type', 'woocommerce-schedule-stock-manager'),
				'value' => get_post_meta(get_the_ID(), 'wssmgk_stock', true),
				'custom_attributes' => $custom_attr
			)
		);
		if ($wssmgk_schedule == 'wssmgk_custom_date') {
			$wssmgk_schedule_display = "style='display:block'";
		} else {
			$wssmgk_schedule_display = "style='display:none'";
		}
		?>
		<!-- /* Date and time input */ -->
		<fieldset class="form-field wssmgk_select_start_time" <?php if ($wssmgk_schedule == '') {
			echo 'style="display:none"';
		} ?>>
			<legend class="default"><img src="<?php echo WSSMGK_URL . '/library/assets/images/crown.png'; ?>"
					alt="crown icon">Start DateTime</legend>
			<legend class="custom_date_and_time" <?php echo $wssmgk_schedule_display; ?>><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/crown.png'; ?>" alt="crown icon">Select DateTime
			</legend>
			<span class="screen-reader-text wssmgkp_start_yy">Year</span>
			<input type="text" id="wssmgkp_date" class="wssmgkp_date" name="wssmgkp_date" value="" placeholder="YYYY-MM-DD"
				maxlength="10" autocomplete="off" disabled>
			<span>@</span>
			<input type="text" id="wssmgkp_hh" class="wssmgkp_hh" name="wssmgkp_hh" placeholder="HH" value="" size="2"
				maxlength="2" autocomplete="off" disabled><span>:</span>
			<input type="text" id="wssmgkp_mn" class="wssmgkp_mn" name="wssmgkp_mn" placeholder="MM" value="" size="2"
				maxlength="2" autocomplete="off" disabled>
			<span>GMT</span>
			<?php $now = time(); ?>
			<span class="form-field wsds_note wssmgk-alert-warning wsds_note default"><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('The Start DateTime option is available in', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro Version
						Only.</a>&nbsp;&nbsp;<b><?php echo esc_html__('By default, it is start with current time.', 'woocommerce-schedule-stock-manager'); ?></b></i></span>
			<span class="form-field wsds_note wssmgk-alert-warning custom_date_and_time" <?php echo $wssmgk_schedule_display; ?>><img src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('Custom Date and Time option is available on', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro Version</a></i></span>
		</fieldset>
		<?php
		// Schedule quantity type
		woocommerce_wp_radio(
			array(
				'id' => 'wssmgkp_disable',
				'label' => __('<img src="' . WSSMGK_URL . '/library/assets/images/crown.png" alt="crown icon">Schedule quantity type', 'woocommerce-schedule-stock-manager'),
				'class' => 'wssmgkp_schedule_qty_type_check',
				'options' => array(
					'add_stock_quantity' => __('Add Stock Quantity', 'woocommerce-schedule-stock-manager'),
					'update_stock_quantity' => __('Update Stock Quantity', 'woocommerce-schedule-stock-manager')
				),
				'desc_tip' => 'true',
				'description' => __('This will add or update schedule quantity to stock quntity. default this will add schedule quntity to stock quantity', 'woocommerce-schedule-stock-manager'),
				'value' => 'add_stock_quantity'
			)
		);
		?>

		<p class="form-field wssmgk_schedule_quantity_type">
			<span class="form-field wsds_note wssmgk-alert-warning"><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('Update Stock Quantity option is available on', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro
						Version</a>&nbsp;&nbsp;<b><?php echo esc_html__('By default it will Add Stock quantity.', 'woocommerce-schedule-stock-manager'); ?></b></i></span>
		</p>
		<?php
		echo '</div></div>';
	}
	static function wssmgk_save_main_product_options($id)
	{
		$wssmgk_schedule_mode = $wssmgk_schedule = $manage_stock = "";
		$schedule_time = wssmgk_schedule_stock_manager::get_schedule_time();
		if (isset($_POST['_manage_stock']))
			$manage_stock = sanitize_text_field($_POST['_manage_stock']);
		if (isset($_POST['wssmgk_schedule_mode']))
			$wssmgk_schedule_mode = sanitize_text_field($_POST['wssmgk_schedule_mode']);
		$wssmgk_old_schedule = get_post_meta($id, 'wssmgk_schedule', true);
		if (isset($_POST['wssmgk_schedule']))
			$wssmgk_schedule = sanitize_text_field($_POST['wssmgk_schedule']);
		$wssmgk_stock = (isset($_POST['wssmgk_stock'])) ? sanitize_text_field($_POST['wssmgk_stock']) : "";
		$wssmgk_old_stock = get_post_meta($id, 'wssmgk_schedule', true);
		$wssmgk_schedule_event = wp_get_schedule('my_hourly_event', array($id));

		if ($manage_stock == 'yes' && $wssmgk_schedule_mode == 'yes') {

			if ($wssmgk_schedule !== "wssmgk_custom_date") {
				update_post_meta($id, 'wssmgk_schedule_mode', $wssmgk_schedule_mode);
				update_post_meta($id, 'wssmgk_schedule', $wssmgk_schedule);
				update_post_meta($id, 'wssmgk_stock', $wssmgk_stock);
			} else {
				update_post_meta($id, 'wssmgk_schedule_mode', 'no');
				update_post_meta($id, 'wssmgk_schedule', '');
				update_post_meta($id, 'wssmgk_stock', "");
			}

		} else {
			update_post_meta($id, 'wssmgk_schedule_mode', 'no');
			update_post_meta($id, 'wssmgk_schedule', '');
			update_post_meta($id, 'wssmgk_stock', "");
		}
		if ($wssmgk_schedule_mode == 'yes' && !empty($wssmgk_schedule) && $manage_stock == 'yes' && $wssmgk_schedule !== "wssmgk_custom_date" && $wssmgk_stock != "") {
			if ($wssmgk_old_schedule != $wssmgk_schedule || $wssmgk_old_stock != $wssmgk_stock || empty($wssmgk_schedule_event)) {
				wp_clear_scheduled_hook('my_hourly_event', array($id));
				wp_schedule_event(time() + $schedule_time[$wssmgk_schedule], $wssmgk_schedule, 'my_hourly_event', array($id));
			}
		} else {
			wp_clear_scheduled_hook('my_hourly_event', array($id));
		}

	}

	static function wssmgk_variation_product_options($loop, $variation_data, $variation)
	{
		$recurrence_type = wssmgk_schedule_stock_manager::get_schedule_type();
		//Auto Manage Stock
		$schedule_mode = get_post_meta($variation->ID, 'wssmgk_schedule_mode', true);
		$wssmgk_schedule = get_post_meta($variation->ID, 'wssmgk_schedule', true);
		$custom_attr = array(
			"min" => 0,
			"oninput" => "this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null",
		);
		if ($schedule_mode == 'yes') {
			$display = "style='display:block'";
			$custom_attr['required'] = 'required';
		} else {
			$display = "style='display:none'";

		}
		
		echo '<div class="show_if_variation_manage_stock wssmgk_variation_opt"' . $display . '>';

		woocommerce_wp_checkbox(array(
			'id' => 'wssmgk_schedule_mode[' . $variation->ID . ']',
			'class' => 'add-required',
			'value' => get_post_meta($variation->ID, 'wssmgk_schedule_mode', true),
			'label' => '&nbsp;&nbsp;Schedule Stock Manage ?',
			'desc_tip' => 'true',
			'description' => 'Enable to auto add stock quantity as per you choose Schedule Type',
		));
		echo '<div class="wssmgk_advance_option" ' . $display . '>';
		// Recurrence Type
		woocommerce_wp_select(
			array(
				'id' => 'wssmgk_schedule[' . $variation->ID . ']',
				'class' => 'wssmgk_variation_schedule',
				'label' => __('Schedule Type', 'woocommerce-schedule-stock-manager'),
				'desc_tip' => 'true',
				'description' => __('This will be executed on a specific interval', 'woocommerce-schedule-stock-manager'),
				'options' => $recurrence_type,
				'value' => get_post_meta($variation->ID, 'wssmgk_schedule', true)
			)
		);
		// Stock quantity
		woocommerce_wp_text_input(
			array(
				'id' => 'wssmgk_stock[' . $variation->ID . ']',
				'label' => __('Stock quantity', 'woocommerce-schedule-stock-manager'),
				'desc_tip' => 'true',
				'class' => 'wssmgk_stock_check',
				'type' => 'number',
				'description' => __('This Stock Quanity will be added on main stock as per you chosen Schedule Type', 'woocommerce-schedule-stock-manager'),
				'value' => get_post_meta($variation->ID, 'wssmgk_stock', true),
				'custom_attributes' => $custom_attr
			)
		);
		if ($wssmgk_schedule == 'wssmgk_custom_date') {
			$wssmgk_schedule_display = "style='display:block'";
		} else {
			$wssmgk_schedule_display = "style='display:none'";
		}
		?>
		<!-- /* Date and time input */ -->
		<fieldset class="form-field wssmgk_select_start_time" <?php if ($wssmgk_schedule == '') {
			echo 'style="display:none"';
		} ?>>
			<legend class="default"><img src="<?php echo WSSMGK_URL . '/library/assets/images/crown.png'; ?>"
					alt="crown icon">Start DateTime</legend>
			<legend class="custom_date_and_time" <?php echo $wssmgk_schedule_display; ?>><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/crown.png'; ?>" alt="crown icon">Select DateTime
			</legend>
			<span class="screen-reader-text wssmgkp_start_yy">Year</span>
			<input type="text" id="wssmgkp_date[<?php esc_attr_e($variation->ID); ?>]" class="wssmgkp_date" name="wssmgkp_date"
				value="" placeholder="YYYY-MM-DD" maxlength="10" autocomplete="off" disabled>
			<span>@</span>
			<input type="text" id="wssmgkp_hh[<?php esc_attr_e($variation->ID); ?>]" class="wssmgkp_hh" name="wssmgkp_hh"
				placeholder="HH" value="" size="2" maxlength="2" autocomplete="off" disabled><span>:</span>
			<input type="text" id="wssmgkp_mn[<?php esc_attr_e($variation->ID); ?>]" class="wssmgkp_mn" name="wssmgkp_mn"
				placeholder="MM" value="" size="2" maxlength="2" autocomplete="off" disabled>
			<span>GMT</span>
			<?php $now = time(); ?>
			<span class="form-field wsds_note wssmgk-alert-warning wsds_note default"><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('The Start DateTime option is available in', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro Version
						Only.</a>&nbsp;&nbsp;<b><?php echo esc_html__('By default it will Add Stock quantity.', 'woocommerce-schedule-stock-manager'); ?></b></i></span>
			<span class="form-field wsds_note wssmgk-alert-warning custom_date_and_time" <?php echo $wssmgk_schedule_display; ?>><img src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('Custom Date and Time option is available on', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro Version</a></i></span>
		</fieldset>
		<?php
		// Schedule quantity type
		woocommerce_wp_radio(
			array(
				'id' => 'wssmgkp_disable_variable',
				'class' => 'wssmgkp_schedule_qty_type_check',
				'label' => __('<img src="' . WSSMGK_URL . '/library/assets/images/crown.png" alt="crown icon">Schedule quantity type', 'woocommerce-schedule-stock-manager'),
				'options' => array(
					'add_stock_quantity' => __('Add Stock Quantity', 'woocommerce-schedule-stock-manager'),
					'update_stock_quantity' => __('Update Stock Quantity', 'woocommerce-schedule-stock-manager')
				),
				'desc_tip' => 'true',
				'description' => __('This will add or update schedule quantity to stock quntity. default this will add schedule quntity to stock quantity', 'woocommerce-schedule-stock-manager'),
				'value' => 'add_stock_quantity'
			)
		);
		?>
		<p class="form-field wssmgk_schedule_quantity_type">
			<span class="form-field wsds_note wssmgk-alert-warning"><img
					src="<?php echo WSSMGK_URL . '/library/assets/images/info_icon.png'; ?>" alt="info icon"> <b>Note:</b>
				<i><?php echo esc_html__('Update Stock Quantity option is available on', 'woocommerce-schedule-stock-manager'); ?>
					<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/"
						target="_blank">Pro
						Version</a>&nbsp;&nbsp;<b><?php echo esc_html__('By default it will Add Stock quantity.', 'woocommerce-schedule-stock-manager'); ?></b></i></span>
		</p>
		<?php
		echo '</div></div>';
	}

	static function wssmgk_save_variation_product_options($post_id)
	{

		$wssmgk_schedule_mode = $wssmgk_schedule = $wssmgk_stock = "";
		$schedule_time = wssmgk_schedule_stock_manager::get_schedule_time();
		$manage_stock = get_post_meta($post_id, '_manage_stock', true);
		$wssmgk_old_schedule = get_post_meta($post_id, 'wssmgk_schedule', true);
		if (isset($_POST['wssmgk_schedule_mode'][$post_id]))
			$wssmgk_schedule_mode = sanitize_text_field($_POST['wssmgk_schedule_mode'][$post_id]);
		if (isset($_POST['wssmgk_schedule'][$post_id]))
			$wssmgk_schedule = sanitize_text_field($_POST['wssmgk_schedule'][$post_id]);
		if (isset($_POST['wssmgk_stock'][$post_id]))
			$wssmgk_stock = (isset($_POST['wssmgk_stock'][$post_id])) ? sanitize_text_field($_POST['wssmgk_stock'][$post_id]) : "";
		$wssmgk_old_stock = get_post_meta($post_id, 'wssmgk_schedule', true);
		$wssmgk_schedule_event = wp_get_schedule('my_hourly_event', array($post_id));

		if ($manage_stock == 'yes' && $wssmgk_schedule_mode == 'yes') {
			if ($wssmgk_schedule !== "wssmgk_custom_date") {
				update_post_meta($post_id, 'wssmgk_schedule_mode', $wssmgk_schedule_mode);
				update_post_meta($post_id, 'wssmgk_schedule', $wssmgk_schedule);
				update_post_meta($post_id, 'wssmgk_stock', $wssmgk_stock);
			} else {
				update_post_meta($post_id, 'wssmgk_schedule_mode', 'no');
				update_post_meta($post_id, 'wssmgk_schedule', '');
				update_post_meta($post_id, 'wssmgk_stock', '');
			}
		} else {
			update_post_meta($post_id, 'wssmgk_schedule_mode', 'no');
			update_post_meta($post_id, 'wssmgk_schedule', '');
			update_post_meta($post_id, 'wssmgk_stock', '');
		}

		if ($wssmgk_schedule_mode == 'yes' && !empty($wssmgk_schedule) && $manage_stock == 'yes' && $wssmgk_schedule !== "wssmgk_custom_date" && $wssmgk_stock != "") {
			if ($wssmgk_old_schedule != $wssmgk_schedule || $wssmgk_old_stock != $wssmgk_stock || empty($wssmgk_schedule_event)) {
				wp_clear_scheduled_hook('my_hourly_event', array($post_id));
				wp_schedule_event(time() + $schedule_time[$wssmgk_schedule], $wssmgk_schedule, 'my_hourly_event', array($post_id));
			}
		} else {
			wp_clear_scheduled_hook('my_hourly_event', array($post_id));
		}

	}

	static function wssmgk_enqueue_admin_scripts()
	{
		wp_enqueue_style('wssmgk_stock_admin_styles', plugins_url('assets/css/styles-admin.css', __FILE__), array(), WSSMGK_BUILD);
		wp_enqueue_script('wssmgk_stock_admin_scripts', plugins_url('assets/js/scripts-admin.js', __FILE__), array(), WSSMGK_BUILD);
	}
}
