<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCQP_Frontend {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('woocommerce_after_add_to_cart_button', [$this, 'add_quick_purchase_button']);
        add_action('wp_footer', [$this, 'inject_popup']);
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'wc-quick-purchase-css',
            WC_QUICK_PURCHASE_URL . 'assets/style.css',
            [],
            WC_QUICK_PURCHASE_VERSION
        );
        wp_enqueue_script(
            'wc-quick-purchase-js',
            WC_QUICK_PURCHASE_URL . 'assets/script.js',
            ['jquery'],
            WC_QUICK_PURCHASE_VERSION,
            true
        );
        wp_localize_script('wc-quick-purchase-js', 'wcqp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);

        $whatsapp_number = get_option('mpb_campo', '');
        wp_localize_script('wc-quick-purchase-js', 'mpbData', array(
            'whatsappNumber' => $whatsapp_number
        ));
    }

    public function add_quick_purchase_button() {
        global $product;
        if (! $product) return;
        echo '<button type="button" class="button wc-quick-purchase-btn" data-product-id="' . esc_attr($product->get_id()) . '">Compra rápida</button>';
    }

    public function inject_popup() {
        if (! is_product()) return;
        global $product;
        if (! $product) return;

        $variations = [];
        if ($product->is_type('variable')) {
            foreach ($product->get_available_variations() as $variation) {
                $variation_obj = wc_get_product($variation['variation_id']);
                $variation['formatted_price'] = wc_price($variation_obj->get_price());
                $variations[] = $variation;
            }
        }
        $attributes = $product->get_variation_attributes();
        ?>
        <div id="wc-quick-purchase-popup" class="wcqp-popup" style="display:none;">
            <div class="wcqp-popup-content">
                <span class="wcqp-close">&times;</span>
                <h3 class="wcqp-title">COMPLETA ESTE FORMULARIO PARA REALIZAR TU PEDIDO</h3>

                <div class="wcqp-attributes"></div>

                <p class="wcqp-price">Precio: <?php echo wp_kses_post($product->get_price_html()); ?></p>
                <label for="wcqp-qty" class="wcqp-qty-label">Cantidad</label>
                <input type="number" id="wcqp-qty" name="qty" min="1" value="1" required>

                <form id="wcqp-form">
                    <div class="wcqp-row">
                        <div class="wcqp-field">
                            <label for="wcqp-name">Nombre *</label>
                            <input type="text" id="wcqp-name" name="name" placeholder="Tu nombre" required>
                        </div>
                        <div class="wcqp-field">
                            <label for="wcqp-lastname">Apellido *</label>
                            <input type="text" id="wcqp-lastname" name="lastname" placeholder="Tu apellido" required>
                        </div>
                    </div>

                    <label for="wcqp-phone">WhatsApp *</label>
                    <input type="tel" id="wcqp-phone" name="phone" placeholder="Ej: 300 965 11 22" required>

                    <label for="wcqp-address">Dirección *</label>
                    <input type="text" id="wcqp-address" name="address" placeholder="Ej: Calle12 #34-65" required>

                    <label for="wcqp-state">Departamento *</label>
                    <input type="text" id="wcqp-state" name="state" required>

                    <label for="wcqp-city">Ciudad *</label>
                    <input type="text" id="wcqp-city" name="city" required>

                    <label for="wcqp-email">Correo electrónico</label>
                    <input type="email" id="wcqp-email" name="email" placeholder="Opcional">

                    <p class="wcqp-total">Pagarás al recibir: 
                        <span id="wcqp-total-amount"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    </p>

                    <input type="hidden" id="wcqp-total-input" name="total" value="0">


                    <button type="submit" class="wcqp-submit">FINALIZAR PEDIDO</button>
                </form>
            </div>
        </div>
        <script>
        window.wcqpData = {
            productId: <?php echo esc_js($product->get_id()); ?>,
            variations: <?php echo wp_json_encode($variations); ?>,
            attributes: <?php echo wp_json_encode($attributes); ?>
        };
        </script>
        <?php
    }
}
