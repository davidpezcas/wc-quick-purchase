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
                'mpb_texto_precio'     => 'Texto antes del precio total',
                'mpb_background'       => 'Fondo',
                'mpb_alt_text'         => 'Texto alternativo',
                'mpb_alt_button_color' => 'Color Boton alternativo',
                'mpb_alt_button_text'  => 'Texto del Boton alternativo'
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

            //Guardar checkboxes como 1 o 0
            update_option('wcqp_enable_alt_text', isset($_POST['wcqp_enable_alt_text']) ? 1 : 0);
            update_option('wcqp_enable_alt_button', isset($_POST['wcqp_enable_alt_button']) ? 1 : 0);

            // Guardar los valores de los campos dependientes
            update_option('mpb_alt_text', sanitize_text_field($_POST['mpb_alt_text'] ?? ''));
            update_option('mpb_alt_button_text', sanitize_text_field($_POST['mpb_alt_button_text'] ?? ''));
            update_option('mpb_alt_button_color', sanitize_hex_color($_POST['mpb_alt_button_color'] ?? ''));

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
        $valor_background      = get_option('mpb_background', '');

        $valor_alt_text  = get_option('mpb_alt_text', '');
        $valor_alt_button_color  = get_option('mpb_alt_button_color', '');
        $valor_alt_button_text  = get_option('mpb_alt_button_text', '');


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
                    <tr valign="top">
                        <th scope="row">Fondo</th>
                        <td>
                            <input type="color" name="mpb_background" value="<?php echo esc_attr($valor_background); ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Texto alternativo</th>
                        <td>
                            <label>
                            <input type="checkbox" id="wcqp_enable_alt_text" name="wcqp_enable_alt_text" value="1" <?php checked( get_option('wcqp_enable_alt_text'), 1 ); ?>>
                            Agregar texto alternativo
                            </label>
                            <div id="mpb_alt_text_container" style="margin-top:8px; display:<?php echo get_option('wcqp_enable_alt_text') ? 'block' : 'none'; ?>;">
                            <input type="text" name="mpb_alt_text" value="<?php echo esc_attr($valor_alt_text); ?>" class="regular-text" placeholder="Texto alternativo">
                            </div>
                        </td>
                        </tr>

                        <tr valign="top">
                        <th scope="row">Botón alternativo</th>
                        <td>
                            <label>
                            <input type="checkbox" id="wcqp_enable_alt_button" name="wcqp_enable_alt_button" value="1" <?php checked( get_option('wcqp_enable_alt_button'), 1 ); ?>>
                            Usar botón alternativo
                            </label>
                            <div id="mpb_alt_button_container" style="margin-top:8px; display:<?php echo get_option('wcqp_enable_alt_button') ? 'block' : 'none'; ?>;">
                            <input type="text" name="mpb_alt_button_text" value="<?php echo esc_attr($valor_alt_button_text); ?>" class="regular-text" placeholder="Texto del botón alternativo"><br><br>
                            <label>Color del botón:</label><br>
                            <input type="color" name="mpb_alt_button_color" value="<?php echo esc_attr($valor_alt_button_color); ?>">
                            </div>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Guardar', 'primary', 'mpb_guardar'); ?>
            </form>
        </div>

        <script>
document.addEventListener('DOMContentLoaded', function() {
  const altTextCheckbox = document.getElementById('wcqp_enable_alt_text');
  const altTextContainer = document.getElementById('mpb_alt_text_container');
  const altBtnCheckbox = document.getElementById('wcqp_enable_alt_button');
  const altBtnContainer = document.getElementById('mpb_alt_button_container');

  altTextCheckbox.addEventListener('change', () => {
    altTextContainer.style.display = altTextCheckbox.checked ? 'block' : 'none';
  });

  altBtnCheckbox.addEventListener('change', () => {
    altBtnContainer.style.display = altBtnCheckbox.checked ? 'block' : 'none';
  });
});
</script>

        <?php
    }
}
