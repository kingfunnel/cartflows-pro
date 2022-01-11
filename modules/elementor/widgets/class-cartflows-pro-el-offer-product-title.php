<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Product Title Widget
 *
 * @since 1.6.13
 */
class Cartflows_Pro_Offer_Product_Title extends Widget_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 1.6.13
	 * @access public
	 * @param string $step_type Current step type.
	 *
	 * @return bool true|false.
	 */
	public static function is_enable( $step_type ) {

		if ( ( 'upsell' === $step_type || 'downsell' === $step_type ) && wcf()->is_woo_active ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.6.13
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'offer-product-title';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.6.13
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Offer Product Title', 'cartflows-pro' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.6.13
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wcf-pro-el-icon-offer-product-title';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.6.13
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'cartflows-widgets' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.6.13
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'offer', 'product', 'title' );
	}

	/**
	 * Register Offer Product Title controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		// Style Tab.
		$this->register_product_title_style_controls();

	}

	/**
	 * Register Offer Product Title Styling Controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function register_product_title_style_controls() {

		$this->start_controls_section(
			'offer_product_title_styling',
			array(
				'label' => __( 'Offer Product Title', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'cartflows-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => __( 'Left', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-right',
					),
					'justify' => array(
						'title' => __( 'Justify', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-justify',
					),
				),
				'default'      => 'left',
				'prefix_class' => 'elementor%s-align-',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'label'    => __( 'Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-title',
			)
		);

		$this->start_controls_tabs( 'offer_product_title_style' );

			$this->start_controls_tab(
				'offer_product_title_normal',
				array(
					'label' => __( 'Normal', 'cartflows-pro' ),
				)
			);

				$this->add_control(
					'text_color',
					array(
						'label'     => __( 'Text Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} .cartflows-pro-elementor__offer-product-title' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => 'text_shadow',
						'label'    => __( 'Text Shadow', 'cartflows-pro' ),
						'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-title',
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'offer_product_title_hover',
				array(
					'label' => __( 'Hover', 'cartflows-pro' ),
				)
			);

				$this->add_control(
					'hover_text_color',
					array(
						'label'     => __( 'Hover Text Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} .cartflows-pro-elementor__offer-product-title:hover' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => 'hover_text_shadow',
						'label'    => __( 'Hover Text Shadow', 'cartflows-pro' ),
						'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-title:hover',
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}


	/**
	 * Render Offer Product Title output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>

		<div class = "cartflows-pro-elementor__offer-product-title">
			<?php echo do_shortcode( '[cartflows_offer_product_title]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Title output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function content_template() {
		?>

		<div class = "cartflows-pro-elementor__offer-product-title">
			<?php echo do_shortcode( '[cartflows_offer_product_title]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Title output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function _content_template() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->content_template();
	}
}
