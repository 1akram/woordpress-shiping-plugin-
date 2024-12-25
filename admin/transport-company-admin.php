<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Transport_Company
 * @subpackage Transport_Company/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Transport_Company
 * @subpackage Transport_Company/admin
 * @author     Your Name <email@example.com>
 */
class Transportation_Company_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_enqueue_scripts', 'my_plugin_check_action_checkbox');

		function my_plugin_check_action_checkbox()
		{
			global $pagenow;
			echo $pagenow;
			wp_enqueue_script('my-plugin-script', plugin_dir_url(__FILE__) . 'js/woocommerce-action-button.js', array('jquery'), '1.0', true);
			if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order') {
			}
		}

		// Add item to menu
		add_action("admin_menu", 	 function () {
			add_menu_page(
				'Transportation',      // Page title
				__('All companies', 'transportation-company-textdomain'),           // Menu title
				'manage_options',      // Capability required to access this menu
				'transportation-companies',           // Slug of the menu
				array($this, 'my_plugin_main_page'), // Callback function to render the page
				'dashicons-car', // Icon URL or Dashicon class
				6                      // Position in the menu order
			);
		});

		// Add JS script to handle transport company logic
		add_action('admin_enqueue_scripts', function () {
			wp_enqueue_script('transport-company-script', plugin_dir_url(__FILE__) . 'js/transport-company-admin.js', ['jquery'], '1.0.0', true);
			wp_localize_script('transport-company-script', 'transportCompanyAjax', [
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('transport_company_nonce')
			]);
		});

		// Handle data update in sqlite database
		add_action('wp_ajax_update_city', function () {
			check_ajax_referer('transport_company_nonce', 'nonce');

			global $wpdb;
			$city_id = intval($_POST['city_id']);
			$column_name = sanitize_text_field($_POST['column_name']);
			$value = sanitize_text_field($_POST['value']);

			if (!in_array($column_name, ['price'])) {
				wp_send_json_error('Invalid column name');
			}

			$updated = $wpdb->update(
				$wpdb->prefix . 'cities',
				[$column_name => $value],
				['id' => $city_id],
				['%s'],
				['%d']
			);

			if ($updated === false) {
				wp_send_json_error('Database update failed');
			}

			wp_send_json_success();
		});


		// Add new action button in the woocommerce orders list
		add_filter(
			'woocommerce_admin_order_actions',
			function ($actions, $order) {
				$actions['custom_action'] = array(
					'url'    => admin_url('admin-ajax.php?action=ship_order&order_id=' . $order->get_id()),
					'name'      => __('ship', 'text-domain'),
					'action'    => 'ship-action',

				);
				return $actions;
			},
			10,
			2
		);

		add_action('admin_footer', function () {
			$screen = get_current_screen();
			if ($screen->id !== 'woocommerce_page_wc-orders') {
				return;
			}
?>
			<div id="custom-modal" class="hidden" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:10000; background:#fff; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.2);">
				<div id="modal-title">
					<h2><?php esc_html_e('Ship order', 'your-textdomain'); ?></h2>
				</div>
				<div id="modal-loading" style="display:none;">
					<p>Loading...</p>
				</div>
				<form id="custom-modal-form">
					<div class="form-row">
						<div>
							<label for="description"><?php esc_html_e('description:', 'your-textdomain'); ?></label><br>
							<input type="text" id="description" name="description" required />
						</div>
						<div class="ui-widget" style="position:relative;z-index: 999999;">
							<label for="city"><?php esc_html_e('city:', 'your-textdomain'); ?></label><br>
							<input type="text" id="city" name="city" required />
						</div>
					</div>
					<div class="form-row">
						<div>
							<label for="reciever"><?php esc_html_e('reciever:', 'your-textdomain'); ?></label><br>
							<input type="text" id="reciever" name="reciever" required />
						</div>
						<div>
							<label for="phone"><?php esc_html_e('phone:', 'your-textdomain'); ?></label><br>
							<input type="text" id="phone" name="phone" required />
						</div>
					</div>
					<input type="hidden" id="order-id" name="order_id" />
					<br><br>
					<button type="submit" class="button button-primary"><?php esc_html_e('Submit', 'your-textdomain'); ?></button>
					<button type="button" class="button button-secondary" id="custom-modal-close"><?php esc_html_e('Close', 'your-textdomain'); ?></button>
				</form>
			</div>

<?php
		});

		add_action('admin_enqueue_scripts', function () {
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

			wp_enqueue_script('custom-ship-button', plugin_dir_url(__FILE__) . 'js/custom-ship-button.js', array('jquery'), '1.0', true);
			wp_localize_script('custom-ship-button', 'orderData', array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ship_order_nonce')
			));
		});

		// Add AJAX action for logged-in users (if needed, you can also create one for guests)
		add_action('wp_ajax_ship_order', function () {
			// Verify the nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ship_order_nonce')) {
				wp_send_json_error('Invalid nonce');
			}

			// Check if the order ID is provided
			if (isset($_POST['order_id'])) {
				$order_id = intval($_POST['order_id']);

				// Get the WooCommerce order
				$order = wc_get_order($order_id);

				// Check if the order exists
				if (!$order) {
					wp_send_json_error('Order not found');
				}

				// Prepare the order data (example: you can customize this as needed)
				$order_data = array(
					'id'            => $order->get_id(),
					'status'        => $order->get_status(),
					'total'         => $order->get_total(),
					'date_created'  => $order->get_date_created()->format('Y-m-d H:i:s'),
					'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
					'date_created' => $order->get_date_created(),
					'date_modified' => $order->get_date_modified(),
					'total' => $order->get_total(),
					'currency' => $order->get_currency(),
					'payment_method' => $order->get_payment_method(),
					'payment_method_title' => $order->get_payment_method_title(),
					'get_user_id' => $order->get_user_id(),
					'billing_first_name' => $order->get_billing_first_name(), // Customer's billing first name
					'billing_last_name' => $order->get_billing_last_name(),  // Customer's billing last name
					'billing_email' => $order->get_billing_email(),      // Customer's billing email
					'billing_phone' => $order->get_billing_phone(),      // Customer's billing phone
					'billing_address_1' => $order->get_billing_address_1(),  // Billing address line 1
					'billing_address_2' => $order->get_billing_address_2(),  // Billing address line 2
					'billing_city' => $order->get_billing_city(),       // Billing city
					'billing_state' => $order->get_billing_state(),      // Billing state
					'billing_postcode' => $order->get_billing_postcode(),   // Billing postcode
					'billing_country' => $order->get_billing_country(),    // Billing country
					'shipping_first_name' => $order->get_shipping_first_name(), // Shipping first name
					'shipping_last_name' => $order->get_shipping_last_name(),  // Shipping last name
					'shipping_address_1' => $order->get_shipping_address_1(),  // Shipping address line 1
					'shipping_address_2' => $order->get_shipping_address_2(),  // Shipping address line 2
					'shipping_city' => $order->get_shipping_city(),       // Shipping city
					'shipping_state' => $order->get_shipping_state(),      // Shipping state
					'shipping_postcode' => $order->get_shipping_postcode(),   // Shipping postcode
					'products' => $order->get_items()
				);
				$order = wc_get_order($order_id);

				// Return the order data as a JSON response
				wp_send_json_success($order_data);
			} else {
				wp_send_json_error('Order ID missing');
			}

			// Always call exit() to stop further processing
			exit;
		});


		add_action('wp_ajax_autocomplete_search', function () {
			global $wpdb;
			$name = sanitize_text_field($_GET['name']);
			$table_name = $wpdb->prefix . 'cities';
			$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE name_en LIKE %s", '%' . $wpdb->esc_like($name) . '%'), OBJECT);

			wp_send_json($results);
		});

		add_action('wp_ajax_request_delivery', function () {
			global $wpdb;
			require_once MY_PLUGIN_DIR . 'includes/transport-company-service.php';

			if (!isset($_POST['order_data']) || !isset($_POST['order_data']['city_name'])) {
				wp_send_json_error(['message' => 'Invalid data received.']);
				wp_die();
			}

			$order_data = $_POST['order_data'];
			$city_name = $order_data['city_name'];

			$table_name = $wpdb->prefix . 'cities';

			$city_id_query = $wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE name_en = %s",
				$city_name
			);
			$city_id_result = $wpdb->get_var($city_id_query);

			if (!$city_id_result) {
				wp_send_json_error(['message' => 'City not found.']);
				wp_die();
			}

			$order_data['city'] = $city_id_result;

			$active_company = get_option('active_company', 'شركة Vanex');

			$classMap = [
				"شركة Vanex" => "Vanex_Transport_Company",
				"شركة المعيار" => "Miaar_Transport_Company",
			];

			if (!isset($classMap[$active_company]) || !class_exists($classMap[$active_company])) {
				wp_send_json_error(['message' => 'Transport company class not found.']);
				wp_die();
			}

			$class_name = $classMap[$active_company];

			$transport_company = new Context(new $class_name());
			$transport_company->requestDelivery($order_data);


			wp_die();
		});
	}

	function my_plugin_main_page()
	{
		include_once(MY_PLUGIN_DIR . 'admin/partials/transport-company-admin-display.php');
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Transport_Company_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Transport_Company_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/transport-company-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Transport_Company_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Transport_Company_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/plugin-name-admin.js', array('jquery'), $this->version, false);
	}
}
