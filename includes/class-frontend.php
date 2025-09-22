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

        $imagen_destacada = get_the_post_thumbnail_url( $product->get_id(), 'medium' );


        ?>

        <?php
        $texto_boton     = get_option('mpb_texto_boton', 'Comprar');
        $color_boton     = get_option('mpb_color_boton', '#000000');
        $color_text_boton = get_option('mpb_color_text_boton', '#FFFFFF');
        $titulo_popup    = get_option('mpb_titulo_popup', 'Título predeterminado');
        $subtitulo_popup = get_option('mpb_subtitulo_popup', 'Subtítulo predeterminado');
        $texto_precio    = get_option('mpb_texto_precio', '');
        $background      = get_option('mpb_background', '');

        // Recuperar las opciones  
        $enable_alt_text    = get_option('wcqp_enable_alt_text', 0);  
        $enable_alt_button  = get_option('wcqp_enable_alt_button', 0);  
        $alt_text           = get_option('mpb_alt_text', '');  
        $alt_button_color   = get_option('mpb_alt_button_color', '#000000');  
        $alt_button_text    = get_option('mpb_alt_button_text', '');  

        ?>
        
        <div id="wc-quick-purchase-popup" class="wcqp-popup" style="display:none;">
            <div class="wcqp-popup-content">
                <span class="wcqp-close">&times;</span>
                <h3 class="wcqp-title"><?php echo esc_html($titulo_popup); ?></h3>

                <div class="group-attributes" style="background-color: <?php echo esc_attr($background); ?>;">
                    <div class="content-attributes">
                        <div class="product-header-popup" style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
                            <?php if ( $imagen_destacada ): ?>
                                <img src="<?php echo esc_url($imagen_destacada); ?>" alt="<?php echo esc_attr( get_the_title( $product->get_id() ) ); ?>" class="wcqp-popup-img">
                            <?php endif; ?>
                            <h4 class="wcqp-product-title" style="margin:0;"><?php echo esc_html( $product->get_name() ); ?></h4>
                        </div>

                        <div class="price-product-popup">
                            <p class="wcqp-price">Precio: <?php echo wp_kses_post($product->get_price_html()); ?></p>
                        </div>
                    </div>    
                    <div class="wcqp-attributes"></div>
                </div>
                <div class="wcqp-qty">
                    <label for="wcqp-qty" class="wcqp-qty-label">Cantidad</label>
                    <input type="number" id="wcqp-qty" name="qty" min="1" value="1" required>
                </div>


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

                    <div class="wcqp-campo">
                        <label for="wcqp-phone">WhatsApp *</label>
                        <input type="tel" id="wcqp-phone" name="phone" placeholder="Ej: 300 965 11 22" required>
                    </div>
                    <p class="wcqp-hint"><?php echo esc_html($subtitulo_popup); ?></p>

                    <div class="wcqp-campo">
                        <label for="wcqp-address">Dirección *</label>
                        <input type="text" id="wcqp-address" name="address" placeholder="Ej: Calle12 #34-65" required>
                    </div>
                    <div class="wcqp-row">
                        <div class="wcqp-field">
                            <label for="wcqp-state">Departamento *</label>
                            <input type="text" id="wcqp-state" name="state" placeholder="Atlantico" required>
                        </div>
                        <div class="wcqp-field">
                            <label for="wcqp-city">Ciudad *</label>
                            <input type="text" id="wcqp-city" name="city" placeholder="Barranquilla" required>
                        </div>
                    </div>
                    <div class="wcqp-campo">
                        <label for="wcqp-email">Correo electrónico</label>
                        <input type="email" id="wcqp-email" name="email" placeholder="tucorreo@correo.com" required>
                    </div>

                    <p class="wcqp-total"><?php echo esc_html($texto_precio); ?> 
                        <span id="wcqp-total-amount"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    </p>

                    <!-- Texto alternativo: solo si el check está activo -->  
                    <?php if ( $enable_alt_text && !empty($alt_text) ) : ?>  
                        <p class="wcqp-alt-text"><?php echo esc_html($alt_text); ?></p>  
                    <?php endif; ?>  

                    <input type="hidden" id="wcqp-total-input" name="total" value="0">

                    <button type="submit" class="wcqp-submit" style="background-color: <?php echo esc_attr($color_boton); ?>; color: <?php echo esc_attr($color_text_boton); ?>;">
                        <?php echo esc_html($texto_boton); ?>
                    </button>

                    <!-- Botón alternativo: solo si el check está activo -->  
                    <?php if ( $enable_alt_button && !empty($alt_button_text) ) : ?>  
                        <a href="#" class="wcqp-buttom" style="background-color: <?php echo esc_attr($alt_button_color); ?>;">  
                            <?php echo esc_html($alt_button_text); ?>  
                        </a>  
                    <?php endif; ?> 

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
