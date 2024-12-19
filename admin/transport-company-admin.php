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
		add_action("admin_menu", array($this, "addAdminMenuItems"));

		add_action('admin_enqueue_scripts', function () {
			wp_enqueue_script('transport-company-script', plugin_dir_url(__FILE__) . 'js/transport-company-admin.js', ['jquery'], '1.0.0', true);
			wp_localize_script('transport-company-script', 'transportCompanyAjax', [
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('transport_company_nonce')
			]);
		});

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

		add_filter('woocommerce_admin_order_actions', array($this, 'add_custom_order_action_button'), 10, 2);

		add_action('admin_head', array($this, 'custom_order_action_buttons_css'));
	}


	public function add_custom_order_action_button($actions, $order)
	{
		$actions['custom_action'] = array(
			'url'       => admin_url('admin.php?page=custom_page&order_id=' . $order->get_id()),
			'name'      => __('ship', 'text-domain'),
			'action'    => 'ship-action',
		);
		return $actions;
	}

	public function custom_order_action_buttons_css()
	{


		echo '
		<style>
			.ship-action::after { 
				font-family: woocommerce !important;  
				content: "\e01a" !important; 
				}
		</style>
		';
	}

	/**
	 * @return [type]
	 */
	public function addAdminMenuItems()
	{
		add_menu_page(
			'Transportation',      // Page title
			'All companies',           // Menu title
			'manage_options',      // Capability required to access this menu
			'transportation-companies',           // Slug of the menu
			array($this, 'my_plugin_main_page'), // Callback function to render the page
			'dashicons-car', // Icon URL or Dashicon class
			6                      // Position in the menu order
		);
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/plugin-name-admin.css', array(), $this->version, 'all');
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/plugin-name-admin.js', array('jquery'), $this->version, false);
	}
}
