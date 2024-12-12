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
            'ajax'     => false
        ]);
    }

    function get_columns()
    {
        return [
            'wilaya'   => 'wilaya',
            'price'   => 'price',
            'home_price'   => 'home price',
        ];
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = [
            ['ID' => 1, 'wilaya' => 'wilaya 1', 'price' => 1500, 'home_price' => 2000],
            ['ID' => 2, 'wilaya' => 'wilaya 2', 'price' => 1500, 'home_price' => 2000],
            ['ID' => 3, 'wilaya' => 'wilaya 3', 'price' => 1500, 'home_price' => 2000],
            ['ID' => 4, 'wilaya' => 'wilaya 4', 'price' => 1500, 'home_price' => 2000],
            ['ID' => 5, 'wilaya' => 'wilaya 5', 'price' => 1500, 'home_price' => 2000],
        ];
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'wilaya':
            case 'price':
            case 'home_price':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }
}
