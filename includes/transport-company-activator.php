<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Transport_Company
 * @subpackage Transport_Company/includes
 */


/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Transport_Company
 * @subpackage Transport_Company/includes
 * @author     Your Name <email@example.com>
 */
class Transport_Company_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 * 
	 */
	public static function activate()
	{
		flush_rewrite_rules();

		// create the cities table
		global $wpdb;

		$table_name = $wpdb->prefix . 'cities';

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name IF NOT EXISTS (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        name_en varchar(255) NOT NULL,
        code varchar(255) NOT NULL,
        price float NOT NULL,
        branch int(255) NOT NULL,
        est_time varchar(255) NOT NULL,
        region varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

		// Include WordPress dbDelta function
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}
}
