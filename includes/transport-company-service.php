<?php

interface Transport_Company
{
    public function authenticate(): string;
    public function insertCity(array $city): void;
    public function requestDelivery(array $data): void;
    public function getCitiesFromDB(): array;
    public function getCitiesFromServer(): array;
}

class Vanex_Transport_Company implements Transport_Company
{
    private string $url;

    public function __construct()
    {
        $this->url =
            $_ENV['VANEX_API'];
    }

    public function authenticate(): string
    {
        $data = ['email' => $_ENV['VANEX_EMAIL'], 'password' => $_ENV['VANEX_PASSWORD']];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/authenticate');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Error during authentication: ' . curl_error($ch));
        }

        curl_close($ch);
        $decoded_response = json_decode($response, true);

        if (!isset($decoded_response['data']['access_token'])) {
            throw new Exception('Authentication failed. Invalid response format.');
        }
        $access_token = $decoded_response['data']['access_token'];
        update_option('active_company_token', $decoded_response['data']['access_token']);
        return $access_token;
    }

    public function insertCity(array $city): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';
        $wpdb->insert(
            $table_name,
            array(
                "id" => $city['id'],
                "name" => $city['name'],
                "name_en" => $city['name_en'],
                "code" => $city['code'],
                "price" => $city['price'],
                "branch" => $city['branch'],
                "est_time" => $city['est_time'],
                "region" => $city['region'],
            )
        );
    }

    public function requestDelivery(array $data): void
    {
        $token = get_option('active_company_token');

        if (!$token) {
            throw new Exception('Authentication token is missing. Please log in again.');
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = wp_remote_post($this->url . '/customer/package', [
            'method'  => 'POST',
            'body'    => json_encode($data),
            'headers' => $headers,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error([
                'message' => $response->get_error_message(),
            ]);
            return;
        }

        // Retrieve and decode the response body.
        $body = wp_remote_retrieve_body($response);
        $decoded_body = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error([
                'message' => 'Invalid response format received from the server.',
            ]);
            return;
        }

        if (isset($decoded_body['status_code']) && $decoded_body['status_code'] === 201) {
            $order_id = get_option('current_order');
            $order = wc_get_order($order_id);
            $order->update_meta_data('package-code', $decoded_body['package_code']);
            $order->save();

            wp_send_json_success([
                'message' =>
                $decoded_body['message'],
            ]);
        } else {
            $error_message = $decoded_body['message'] ?? 'Unknown error occurred while processing the delivery request.';
            wp_send_json_error([
                'message' => $error_message,
            ]);
        }
    }

    function getCitiesFromDB(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';

        $results = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
        return $results;
    }

    public function getCitiesFromServer(): array
    {
        $token = get_option('active_company_token');

        if (!$token) {
            throw new Exception('Authentication token is missing. Please log in again.');
        }

        $headers = [
            'Authorization: Bearer ' . $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/city/names');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Error fetching cities: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded_response = json_decode($response, true);
        return $decoded_response['data'];
    }
}

class Miaar_Transport_Company implements Transport_Company
{
    private string $url;

    public function __construct()
    {
        $this->url =
            $_ENV['MIAAR_API'];
    }

    public function authenticate(): string
    {

        $mutation = <<<GQL
            mutation (\$input: LoginInput!) {
                login(input: \$input) {
                    token
                    ttl
                }
            }
            GQL;
        $data = [
            'query' => $mutation,
            'variables' => [
                'input' => array(
                    "username" => $_ENV['MIAAR_EMAIL'],
                    "password" => $_ENV['MIAAR_PASSWORD'],
                    "rememberMe" => true,
                    "fcmToken" => "fcmToken" // Changed later
                )
            ],
        ];

        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        echo json_encode($data);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            wp_send_json_error([
                'message' => curl_error($ch),
            ]);
            curl_close($ch);
            exit;
        }

        $decoded_response = json_decode($response, true);
        update_option('active_company_token', $decoded_response['token']);

        return $decoded_response;
    }

    public function insertCity(array $city): void {}

    public function requestDelivery(array $data): void {}

    public function getCitiesFromDB(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';

        $results = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
        return $results;
    }

    public function getCitiesFromServer(): array
    {
        return [];
    }
}

class Camex_Transport_Company implements Transport_Company
{
    private string $url;

    public function __construct()
    {
        $this->url =
            $_ENV['CAMEX_API'];
    }

    public function authenticate(): string
    {
        $params = ['providerKey' => $_ENV['CAMEX_PROVIDER_KEY'], 'clientKey' => $_ENV['CAMEX_CLIENT_KEY']];
        $queryString = http_build_query($params);

        $url = $this->url . '/ApiEndpoints/Login?' . $queryString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error during authentication: ' . curl_error($ch));
        }

        curl_close($ch);
        $decoded_response = json_decode($response, true);
        if ($decoded_response['type'] == (2 || 3)) {
            throw new Exception('Authentication failed. error :' . $decoded_response['messages'][0]);
        }
        $access_token = $decoded_response['data']['content']['value'];
        update_option('active_company_token', $access_token);
        return $access_token;
    }

    public function insertCity(array $city): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';
        $wpdb->insert(
            $table_name,
            array(
                "id" => $city['cityId'],
                "name" => $city['cityName'],
                "branch" => $city['areaName'],
                "price" => $city['totalCost'],
            )
        );
    }

    public function requestDelivery(array $data): void
    {
        $token = get_option('active_company_token');

        if (!$token) {
            throw new Exception('Authentication token is missing. Please log in again.');
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
        // $data =[
        //  "cityId"=> $body['id'] 0, 
        //  "customWeight"=> 0,
        //  "noItems"=> count($body['products']) 0,
        //  "price"=> $body['total'] ,
        //  "productDescrp"=> "", 
        //  "storeName"=> "", 
        //  "receiverPhone"=> $body['billing_phone'],
        //  "customWeightMeta"=> "",
        //  "address"=> $body['billing_address_1']." ".$body['billing_address_2'],
        //  "notes"=> "", 
        // ]

        $response = wp_remote_post($this->url . '/ApiEndpoints', [
            'method'  => 'POST',
            'body'    => json_encode($data),
            'headers' => $headers,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error([
                'message' => $response->get_error_message(),
            ]);
            return;
        }

        // Retrieve and decode the response body.
        $body = wp_remote_retrieve_body($response);
        $decoded_body = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error([
                'message' => 'Invalid response format received from the server.',
            ]);
            return;
        }

        if (isset($decoded_body['status_code']) && $decoded_body['status_code'] === 201) {
            $order_id = get_option('current_order');
            $order = wc_get_order($order_id);
            $order->update_meta_data('package-code', $decoded_body['package_code']);
            $order->save();

            wp_send_json_success([
                'message' =>
                $decoded_body['message'],
            ]);
        } else {
            $error_message = $decoded_body['message'] ?? 'Unknown error occurred while processing the delivery request.';
            wp_send_json_error([
                'message' => $error_message,
            ]);
        }
    }

    function getCitiesFromDB(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';

        $results = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
        return $results;
    }

    public function getCitiesFromServer(): array
    {
        $token = get_option('active_company_token');

        if (!$token) {
            throw new Exception('Authentication token is missing. Please log in again.');
        }

        $headers = [
            'Authorization: Bearer ' . $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/ApiEndpoints/Cities?culture=ar-LY');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $decoded_response = json_decode($response, true);
        if ($decoded_response['type'] == (2 || 3)) {
            throw new Exception('Error fetching cities: ' . curl_error($ch));
        }
        return $decoded_response['data']['content'];
    }
}

class Context
{
    private $transport_company;

    public function __construct(Transport_Company $transport_company)
    {
        $this->transport_company = $transport_company;
    }

    public function setTransportCompany(Transport_Company $new_transport_company)
    {
        $this->transport_company = $new_transport_company;
    }

    public function authenticate(): void
    {
        $this->transport_company->authenticate();
    }

    public function insertCities(array $data): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cities';

        $wpdb->query("DELETE FROM $table_name");

        foreach ($data as $city)
            $this->transport_company->insertCity($city);
    }

    public function getCities(): array
    {
        $cities = $this->transport_company->getCitiesFromServer();
        return $cities;
    }

    public function getCitiesFromLocalDB(): array
    {
        $cities = $this->transport_company->getCitiesFromDB();
        return $cities;
    }

    public function requestDelivery(array $data)
    {
        return $this->transport_company->requestDelivery($data);
    }
}
