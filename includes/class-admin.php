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

    /** Renderizar la página del plugin */
    public function mpb_render_admin_page() {
        // Guardar los datos si se envió el formulario
        if ( isset($_POST['mpb_guardar']) ) {
            if ( ! current_user_can('manage_options') ) return;
            check_admin_referer('mpb_guardar_datos');

            // Campos
            $campos = [
                'mpb_campo'            => 'Número de WhatsApp',
                'mpb_texto_boton'      => 'Texto del botón',
                'mpb_color_boton'      => 'Color del botón',
                'mpb_color_text_boton' => 'Color del texto del botón',
                'mpb_titulo_popup'     => 'Título del popup',
                'mpb_subtitulo_popup'  => 'Subtítulo del popup',
                'mpb_texto_precio'     => 'Texto antes del precio total'
            ];

            foreach ( $campos as $key => $label ) {
                if ( $key === 'mpb_color_boton' ) {
                    $valor = sanitize_hex_color($_POST[$key]);
                }if ( $key === 'mpb_color_text_boton') {
                    $valor = sanitize_hex_color($_POST[$key]);
                }else {
                    $valor = sanitize_text_field($_POST[$key]);
                }
                update_option($key, $valor);
            }

            echo '<div class="updated"><p>Datos guardados.</p></div>';
        }

        // Obtener valores guardados
        $valor_campo           = get_option('mpb_campo', '');
        $valor_texto_boton     = get_option('mpb_texto_boton', '');
        $valor_color_boton     = get_option('mpb_color_boton', '#000000');
        $valor_color_text_boton     = get_option('mpb_color_text_boton', '#FFFFFF');
        $valor_titulo_popup    = get_option('mpb_titulo_popup', '');
        $valor_subtitulo_popup = get_option('mpb_subtitulo_popup', '');
        $valor_texto_precio    = get_option('mpb_texto_precio', '');

        ?>
        <div class="wrap">
            <h1>WC Quick Purchase</h1>
            <form method="post">
                <?php wp_nonce_field('mpb_guardar_datos'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Número de WhatsApp</th>
                        <td>
                            <input type="text" name="mpb_campo" value="<?php echo esc_attr($valor_campo); ?>" size="50">
                            <span>Escribe el numero con el indicativo de tu pais sin el signo "+"</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Texto del botón</th>
                        <td>
                            <input type="text" name="mpb_texto_boton" value="<?php echo esc_attr($valor_texto_boton); ?>" size="50">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Color del botón</th>
                        <td>
                            <input type="color" name="mpb_color_boton" value="<?php echo esc_attr($valor_color_boton); ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Color del texto del botón</th>
                        <td>
                            <input type="color" name="mpb_color_text_boton" value="<?php echo esc_attr($valor_color_text_boton); ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Título del popup</th>
                        <td>
                            <input type="text" name="mpb_titulo_popup" value="<?php echo esc_attr($valor_titulo_popup); ?>" size="50">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Subtítulo del popup</th>
                        <td>
                            <input type="text" name="mpb_subtitulo_popup" value="<?php echo esc_attr($valor_subtitulo_popup); ?>" size="50">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Texto antes del precio total</th>
                        <td>
                            <input type="text" name="mpb_texto_precio" value="<?php echo esc_attr($valor_texto_precio); ?>" size="50">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar', 'primary', 'mpb_guardar'); ?>
            </form>
        </div>
        <?php
    }
}
