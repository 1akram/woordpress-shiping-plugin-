<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Transport_Company_List_Table extends WP_List_Table
{
    function __construct()
    {
        parent::__construct([
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false,
        ]);

        // Handle form submission and save changes
        $this->handle_form_submission();
    }

    function get_columns()
    {
        return [
            'name'  => 'City',
            'price' => 'Price',
        ];
    }

    function prepare_items()
    {
        require_once MY_PLUGIN_DIR . 'includes/transport-company-service.php';

        $active_company = get_option('active_company', 'شركة Vanex');
        $cities = [];
        $classMap = [
            "شركة Vanex" => "Vanex_Transport_Company",
            "شركة المعيار" => "Miaar_Transport_Company",
        ];

        if (isset($classMap[$active_company])) {
            $class_name = $classMap[$active_company];

            if (class_exists($class_name)) {
                $transport_company = new Context(new $class_name());
                $cities = json_decode(json_encode($transport_company->getCitiesFromLocalDB()), true);
            } else {
                die('Error: Class for selected company not found.');
            }
        } else {
            die('Error: Selected company is not mapped to any class.');
        }

        // Pagination params
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($cities);

        $this->items = array_slice($cities, ($current_page - 1) * $per_page, $per_page);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
                return esc_html($item['name'] ?? 'N/A');
            case 'price':
                $price = esc_attr($item[$column_name] ?? '0');
                return sprintf(
                    '<input type="text" class="city-edit-field" data-id="%d" data-column="%s" value="%s">',
                    $item['id'],
                    $column_name,
                    esc_attr($price)
                );
            default:
                return print_r($item, true);
        }
    }
}
