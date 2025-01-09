<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Transport_Company
 * @subpackage Transport_Company/admin/partials
 */

require_once TRANSPORT_COMPANY_DIR . 'includes/transport-company-list-table.php';
require_once TRANSPORT_COMPANY_DIR . 'includes/transport-company-service.php';

// Save the selected company when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['companies'])) {
    update_option('active_company', sanitize_text_field($_POST['companies']));

    $classMap = [
        "شركة Vanex" => "Vanex_Transport_Company",
        "شركة المعيار" => "Miaar_Transport_Company",
    ];

    $active_company = get_option('active_company', 'شركة Vanex');

    if (isset($classMap[$active_company])) {
        $class_name = $classMap[$active_company];

        if (class_exists($class_name)) {
            $transport_company = new Context(new $class_name());
            $transport_company->authenticate();
            $cities = $transport_company->getCities();
            $transport_company->insertCities($cities);
        } else {
            die('Error: Class for selected company not found.');
        }
    } else {
        die('Error: Selected company is not mapped to any class.');
    }
}

?>


<div class="wrap">

    <h1><?php esc_html_e('Transport Company', 'transport-company-textdomain'); ?></h1>

    <form method="POST">
        <?php settings_fields('my-plugin-settings-group'); ?>
        <?php do_settings_sections('my-plugin-settings'); ?>


        <div class="form-table">
            <div valign="top" class="company-selection-row">
                <div>
                    <div scope="row" class="company-dropdown-label">
                        <label for="companies-options"><?php esc_html_e('Company', 'transportation-company-textdomain'); ?></label>
                    </div>
                    <div>
                        <select id="companies-options" name="companies" onchange="this.form.submit()">
                            <option value="شركة Vanex" <?php selected(get_option('active_company'), 'شركة Vanex'); ?>>شركة Vanex</option>
                            <option value="شركة المعيار" <?php selected(get_option('active_company'), 'شركة المعيار'); ?>>شركة المعيار</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button class="button-primary" onclick="submit">
                        <?php esc_html_e('Refresh', 'transportation-company-textdomain'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="wrap">
    <?php
    $list_table = new Transport_Company_List_Table();
    $list_table->print_table_description();
    $list_table->prepare_items();
    $list_table->display();
    ?>
</div>