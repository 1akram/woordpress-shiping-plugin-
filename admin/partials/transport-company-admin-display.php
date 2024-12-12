<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
?>

<div class="wrap">
    <h1><?php esc_html_e('My Plugin Settings', 'my-plugin-textdomain'); ?></h1>

    <div>
        <?php settings_fields('my-plugin-settings-group'); ?>
        <?php do_settings_sections('my-plugin-settings'); ?>

        <div class="form-table">
            <div valign="top" style="display: flex;justify-content:space-between;align-items:center">
                <div>
                    <div scope="row" style="margin-bottom: 10px;">
                        <label for="my_plugin_option2"><?php esc_html_e('Company', 'my-plugin-textdomain'); ?></label>
                    </div>
                    <div>
                        <select id="my_plugin_option2" name="my_plugin_options[option2]">
                            <option value="شركة Vanex">شركة Vanex</option>
                            <option value="شركة المعيار">شركة المعيار</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button class="button-primary">
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>