<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCQP_Admin {

    public function __construct() {
        // Verificar que WooCommerce esté activo
        add_action( 'plugins_loaded', [ $this, 'check_woocommerce' ] );

        // Agregar menú de administración
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
    }

    /** Verificar WooCommerce */
    public function check_woocommerce() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="error"><p><strong>WC Quick Purchase</strong> requiere WooCommerce activo.</p></div>';
            });
        }
    }

    /** Crear menú en el admin */
    public function add_admin_menu() {
        add_menu_page(
            'WC Quick Purchase',
            'WC Quick Purchase',
            'manage_options',
            'wc-quick-purchase',
            [ $this, 'mpb_render_admin_page' ],
            'dashicons-admin-generic',
            100
        );
    }

    /**Renderizar la página del plugin */
    public function mpb_render_admin_page() {
        if ( isset($_POST['mpb_campo']) ) {
            if ( ! current_user_can('manage_options') ) return;
            check_admin_referer('mpb_guardar_datos');
            $valor = sanitize_text_field($_POST['mpb_campo']);
            update_option('mpb_campo', $valor);
            echo '<div class="updated"><p>Datos guardados.</p></div>';
        }

        $valor_guardado = get_option('mpb_campo', '');
        ?>
        <div class="wrap">
            <h1>WC Quick Purchase</h1>
            <form method="post">
                <?php wp_nonce_field('mpb_guardar_datos'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Número de Whatsapp</th>
                        <td>
                            <input type="text" name="mpb_campo"
                                   value="<?php echo esc_attr($valor_guardado); ?>"
                                   size="50">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar'); ?>
            </form>
        </div>
        <?php
    }
}
