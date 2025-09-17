<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCQP_Ajax {

    public function __construct() {
        add_action('wp_ajax_wcqp_create_order', [$this, 'wcqp_create_order']);
        add_action('wp_ajax_nopriv_wcqp_create_order', [$this, 'wcqp_create_order']);
    }

    public function wcqp_create_order() {
        if ( ! isset($_POST['product_id'], $_POST['qty'], $_POST['name'], $_POST['phone']) ) {
            wp_send_json_error(['message' => 'Faltan datos necesarios'], 400);
        }

        $product_id = absint($_POST['product_id']);
        $qty        = absint($_POST['qty']);
        $name       = sanitize_text_field($_POST['name']);
        $lastname   = sanitize_text_field($_POST['lastname'] ?? '');
        $phone      = sanitize_text_field($_POST['phone']);
        $email      = sanitize_email($_POST['email'] ?? '');
        $address    = sanitize_text_field($_POST['address'] ?? '');
        $state      = sanitize_text_field($_POST['state'] ?? '');
        $city       = sanitize_text_field($_POST['city'] ?? '');
        $attributes = (array) ($_POST['attributes'] ?? []);

        $order   = wc_create_order();
        $product = wc_get_product($product_id);

        if ($product->is_type('variable') && ! empty($attributes)) {
            $variation_id = $this->find_matching_variation_id($product, $attributes);
            if (! $variation_id) {
                wp_send_json_error(['message' => 'Variación no encontrada'], 400);
            }
            $order->add_product(wc_get_product($variation_id), $qty);
        } else {
            $order->add_product($product, $qty);
        }

        $order->set_address([
            'first_name' => $name,
            'last_name'  => $lastname,
            'phone'      => $phone,
            'email'      => $email,
            'address_1'  => $address,
            'state'      => $state,
            'city'       => $city,
        ], 'billing');

        $order->update_meta_data('_wcqp_full_name', "$name $lastname");
        $order->update_meta_data('_wcqp_phone', $phone);
        $order->update_meta_data('_wcqp_address', $address);
        $order->update_meta_data('_wcqp_city', $city);
        $order->update_meta_data('_wcqp_state', $state);
        $order->update_meta_data('_wcqp_email', $email);

        $order->set_status('pending');
        $order->calculate_totals();
        $order->save();

        wp_send_json_success([
            'message'  => 'Pedido creado',
            'order_id' => $order->get_id(),
        ]);
    }

    private function find_matching_variation_id($product, $attributes) {
        foreach ($product->get_available_variations() as $variation) {
            $match = true;
            foreach ($attributes as $key => $value) {
                if ($variation['attributes']['attribute_' . $key] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $variation['variation_id'];
        }
        return 0;
    }
}
