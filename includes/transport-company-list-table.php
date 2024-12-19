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
        $cities = get_option('cities', []);

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
                return esc_html($item[$column_name] ?? 'N/A');
            case 'price':
                $price = esc_attr($item[$column_name] ?? '0');
                return "<input type='text' name='price[{$item['id']}]' value='{$price}' />";
            default:
                return print_r($item, true);
        }
    }
}
