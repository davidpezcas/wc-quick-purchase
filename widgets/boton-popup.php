<?php
/**
 * Widget Elementor: Botón Popup para WC Quick Purchase
 */

namespace WCQP\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Boton_Popup extends Widget_Base {

    public function get_name() {
        return 'wcqp_boton_popup';
    }

    public function get_title() {
        return __( 'Botón Quick Purchase', 'wc-quick-purchase' );
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return [ 'wcqp-category' ];
    }

    protected function register_controls() {
        // === Sección de contenido ===
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Contenido', 'wc-quick-purchase' ),
            ]
        );

        $this->add_control(
            'texto_boton',
            [
                'label' => __( 'Texto del botón', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Compra rápida', 'wc-quick-purchase' ),
            ]
        );

        $this->end_controls_section();

        // === Sección de estilo ===
        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Estilo del botón', 'wc-quick-purchase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Color de texto
        $this->add_control(
            'color_texto',
            [
                'label' => __( 'Color del texto', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Color de fondo
        $this->add_control(
            'color_fondo',
            [
                'label' => __( 'Color de fondo', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073e6',
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Tipografía
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tipografia',
                'label' => __( 'Tipografía', 'wc-quick-purchase' ),
                'selector' => '{{WRAPPER}} .wcqp-boton-popup',
            ]
        );

        // Borde
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'borde',
                'label' => __( 'Borde', 'wc-quick-purchase' ),
                'selector' => '{{WRAPPER}} .wcqp-boton-popup',
            ]
        );

        // Radio de borde
        $this->add_control(
            'radio_borde',
            [
                'label' => __( 'Radio del borde', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Padding
        $this->add_control(
            'padding',
            [
                'label' => __( 'Padding', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Margin
        $this->add_control(
            'margin',
            [
                'label' => __( 'Margen', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Ancho
        $this->add_responsive_control(
            'ancho',
            [
                'label' => __( 'Ancho', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 50, 'max' => 600 ],
                    '%'  => [ 'min' => 10, 'max' => 100 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Alto
        $this->add_responsive_control(
            'alto',
            [
                'label' => __( 'Alto', 'wc-quick-purchase' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [ 'min' => 20, 'max' => 200 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wcqp-boton-popup' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }


    protected function render() {
        global $product;

        // Obtener ID de producto desde contexto WooCommerce o desde ajustes del widget
        $product_id = $product ? $product->get_id() : 0;

        // Si no hay producto en contexto (por ejemplo, en una página Elementor que no es ficha de producto),
        // puedes permitir asignar un ID manual como control adicional en Elementor (opcional).
        $settings = $this->get_settings_for_display();
        ?>
        <button type="button" 
            class="wcqp-boton-popup" 
            data-product-id="<?php echo esc_attr($product_id); ?>">
            <?php echo esc_html( $settings['texto_boton'] ); ?>
        </button>
        <?php
    }

}
