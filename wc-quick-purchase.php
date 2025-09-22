<?php
/**
 * Plugin Name: WC Quick Purchase
 * Description: Añade un botón "Compra rápida" a WooCommerce para pedidos rápidos vía WhatsApp y creación automática en WooCommerce.
 * Version: 1.0.12
 * Author: David Perez
 * Author URI:  https://github.com/davidpezcas
 * Plugin URI:  https://github.com/davidpezcas/wc-quick-purchase
 * Text Domain: wc-quick-purchase
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constante de versión
if ( ! defined( 'WC_QUICK_PURCHASE_VERSION' ) ) {
    define( 'WC_QUICK_PURCHASE_VERSION', '1.0.12');
}

define( 'WC_QUICK_PURCHASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_QUICK_PURCHASE_URL',  plugin_dir_url( __FILE__ ) );

// includes
require_once WC_QUICK_PURCHASE_PATH . 'includes/class-admin.php';
require_once WC_QUICK_PURCHASE_PATH . 'includes/class-ajax-handler.php';
require_once WC_QUICK_PURCHASE_PATH . 'includes/class-frontend.php';

if ( is_admin() ) {
    require_once WC_QUICK_PURCHASE_PATH . 'includes/class-github-updater.php';

    // Conectar el plugin a GitHub
    $repo_url = 'https://github.com/davidpezcas/wc-quick-purchase';
    new GitHub_Updater(__FILE__, $repo_url);
}

// Inicializar clases
add_action( 'plugins_loaded', function() {
    new WCQP_Admin();
    new WCQP_Ajax();
    new WCQP_Frontend();

     // Solo registrar el widget si Elementor está activo
    if ( did_action('elementor/loaded') ) {

        //Registrar la categoría personalizada para Elementor
        add_action('elementor/elements/categories_registered', function($elements_manager){
            $elements_manager->add_category(
                'wcqp-category', // ID de la categoría
                [
                    'title' => __( 'WC Quick Purchase', 'wc-quick-purchase' ), // Nombre visible
                    'icon'  => 'eicon-cart', // Icono opcional
                ]
            );
        });

        //Registrar el widget en esa categoría
        add_action('elementor/widgets/register', function($widgets_manager){
            require_once __DIR__ . '/widgets/boton-popup.php';
            $widgets_manager->register(new \WCQP\Widgets\Boton_Popup());
        });
    }
});
