<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
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
		require_once MY_PLUGIN_DIR . 'includes/transport-company-list-table.php';

		echo '<div class="wrap">';
		$list_table = new Transport_Company_List_Table();
		$list_table->print_table_description();
		$list_table->prepare_items();
		$list_table->display();
		echo '</div>';
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
