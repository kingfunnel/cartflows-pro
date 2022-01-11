<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Widget_Base;
use Elementor\Widget_Button;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Color;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Yes No Button Widget
 *
 * @since 1.6.13
 */
class Cartflows_Pro_Offer_Action_Button extends Widget_Base {

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
		return 'offer-yes-no-button';
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
		return __( 'Offer Yes/No Button', 'cartflows-pro' );
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
		return 'wcf-pro-el-icon-yes-no-button';
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
		return array( 'cartflows', 'offer', 'product', 'offer button', 'yes', 'no', 'button' );
	}

	/**
	 * Retrieve Offer Yes/No Button sizes.
	 *
	 * @since 1.6.13
	 * @access public
	 *
	 * @return array Offer Yes/No Button Sizes.
	 */
	public static function get_button_sizes() {
		return Widget_Button::get_button_sizes();
	}

	/**
	 * Register Offer Yes/No Button controls controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		// Content Tab.
		$this->register_button_content_controls();

		// Style Tab.
		$this->register_button_style_controls();
		$this->register_button_content_style_controls();

	}

	/**
	 * Register Offer Yes/No Button General Controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function register_button_content_controls() {

		$this->start_controls_section(
			'button_content_fields',
			array(
				'label' => __( 'General', 'cartflows-pro' ),
			)
		);

		$this->add_control(
			'offer_action',
			array(
				'label'   => __( 'Offer Action', 'cartflows-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'accept_offer',
				'options' => array(
					'accept_offer' => __( 'Accept Offer', 'cartflows-pro' ),
					'reject_offer' => __( 'Reject Offer', 'cartflows-pro' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'cartflows-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Offer Action', 'cartflows-pro' ),
			)
		);

		$this->add_control(
			'sub_title',
			array(
				'label' => __( 'Sub Title', 'cartflows-pro' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$this->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'cartflows-pro' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'cartflows-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before_title',
				'options'   => array(
					'before_title'           => __( 'Before Title', 'cartflows-pro' ),
					'after_title'            => __( 'After Title', 'cartflows-pro' ),
					'before_title_sub_title' => __( 'Before Title & Sub Title', 'cartflows-pro' ),
					'after_title_sub_title'  => __( 'After Title & Sub Title', 'cartflows-pro' ),
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'icon_vertical_align',
			array(
				'label'       => __( 'Icon Vertical Alignment', 'cartflows-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'center',
				'options'     => array(
					'flex-start' => array(
						'title' => __( 'Top', 'cartflows-pro' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Middle', 'cartflows-pro' ),
						'icon'  => 'eicon-v-align-middle',
					),
				),
				'condition'   => array(
					'icon_position' => array( 'before_title_sub_title', 'after_title_sub_title' ),
					'icon[value]!'  => '',
				),
				'selectors'   => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-icon-wrap' => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_spacing',
			array(
				'label'     => __( 'Icon Spacing', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-title-wrap .cartflows-pro-elementor__before_title, {{WRAPPER}} .cartflows-pro-elementor__before_title_sub_title' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-title-wrap .cartflows-pro-elementor__after_title, {{WRAPPER}} .cartflows-pro-elementor__after_title_sub_title' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon Size', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-wrap i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-wrap svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register Offer Yes/No Button Styling Controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function register_button_style_controls() {

		$this->start_controls_section(
			'offer_yes_no_button_styling',
			array(
				'label' => __( 'General', 'cartflows-pro' ),
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

		$this->add_control(
			'button_size',
			array(
				'label'   => __( 'Button Size', 'cartflows-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => self::get_button_sizes(),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'cartflows-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'offer_yes_no_button_style' );

			$this->start_controls_tab(
				'offer_yes_no_button_normal',
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
							'{{WRAPPER}} a.cartflows-pro-elementor__offer-yes-no-button-link' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'           => 'all_background_color',
						'label'          => __( 'Background Color', 'cartflows-pro' ),
						'types'          => array( 'classic', 'gradient' ),
						'selector'       => '{{WRAPPER}} a.elementor-button',
						'fields_options' => array(
							'color' => array(
								'scheme' => array(
									'type'  => Color::get_type(),
									'value' => Color::COLOR_4,
								),
							),
						),
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'     => 'all_border',
						'label'    => __( 'Border', 'cartflows-pro' ),
						'selector' => '{{WRAPPER}} .elementor-button',
					)
				);

				$this->add_control(
					'all_border_radius',
					array(
						'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%' ),
						'selectors'  => array(
							'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'offer_yes_no_button_hover',
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
							'{{WRAPPER}} a.cartflows-pro-elementor__offer-yes-no-button-link:hover' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'           => 'hover_background_color',
						'label'          => __( 'Hover Background Color', 'cartflows-pro' ),
						'types'          => array( 'classic', 'gradient' ),
						'selector'       => '{{WRAPPER}} a.elementor-button:hover',
						'fields_options' => array(
							'color' => array(
								'scheme' => array(
									'type'  => Color::get_type(),
									'value' => Color::COLOR_4,
								),
							),
						),
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Register Button Content Styling Controls.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function register_button_content_style_controls() {

		$this->start_controls_section(
			'button_content_styling',
			array(
				'label' => __( 'Content', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_align',
			array(
				'label'     => __( 'Text Alignment', 'cartflows-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} a.elementor-button' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'align' => 'justify',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-title',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'sub_title_typography',
				'label'     => __( 'Sub Title Typography', 'cartflows-pro' ),
				'scheme'    => Typography::TYPOGRAPHY_3,
				'selector'  => '{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-sub-title',
				'condition' => array(
					'sub_title!' => '',
				),
			)
		);

		$this->add_control(
			'title_sub_title_spacing',
			array(
				'label'     => __( 'Title and Sub Title Spacing', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-button-sub-title' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'sub_title!' => '',
				),
			)
		);

	}

	/**
	 * Render Offer Yes/No Button icon.
	 *
	 * @since 1.6.13
	 * @param string $position Icon position.
	 * @param array  $settings settings.
	 * @access protected
	 */
	protected function render_button_icon( $position, $settings ) {

		$icon                = esc_attr( $settings['icon']['value'] );
		$icon_position_class = esc_attr( 'cartflows-pro-elementor__' . $settings['icon_position'] );

		if ( '' !== $icon && $position === $settings['icon_position'] ) {
			?>
			<span class="cartflows-pro-elementor__offer-yes-no-button-icon-wrap <?php echo $icon_position_class; ?>">
				<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
			</span>
			<?php
		}

	}

	/**
	 * Render Yes No Button output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function render() {
		$settings    = $this->get_settings_for_display();
		$button_size = esc_attr( $settings['button_size'] );
		$title       = esc_html( $settings['title'] );
		$sub_title   = esc_html( $settings['sub_title'] );
		$step_type   = get_post_meta( get_the_ID(), 'wcf-step-type', true );
		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {
			$accept_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-yes' : '?class=wcf-down-offer-yes';
			$reject_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-no' : '?class=wcf-down-offer-no';
		}
		$action_link = 'accept_offer' === $settings['offer_action'] ? $accept_offer_link : $reject_offer_link;
		?>

		<div class="cartflows-pro-elementor__offer-yes-no-button">
			<div class="cartflows-pro-elementor__offer-yes-no-button-wrap">
				<a href="<?php echo $action_link; ?>" class="cartflows-pro-elementor__offer-yes-no-button-link elementor-button elementor-button-link elementor-size-<?php echo $button_size; ?>">
					<div class="cartflows-pro-elementor__offer-yes-no-inner-wrap">
						<?php $this->render_button_icon( 'before_title_sub_title', $settings ); ?>
							<span class="cartflows-pro-elementor__offer-yes-no-button-content-wrap">
								<div class="cartflows-pro-elementor__offer-yes-no-button-title-wrap">
									<?php $this->render_button_icon( 'before_title', $settings ); ?>
										<span class="cartflows-pro-elementor__offer-yes-no-button-title"><?php echo $title; ?></span>
									<?php $this->render_button_icon( 'after_title', $settings ); ?>
								</div>
								<?php if ( '' !== $settings['sub_title'] ) { ?>
									<div class="cartflows-pro-elementor__offer-yes-no-button-sub-title"><?php echo $sub_title; ?></div>
								<?php } ?>
							</span>
						<?php $this->render_button_icon( 'after_title_sub_title', $settings ); ?>
					</div>
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Render Menu Offer Yes No Button output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	protected function content_template() {
		?>

		<#
			function render_icon(position) {

				var iconHTML = elementor.helpers.renderIcon( view, settings.icon, {}, 'i' , 'object' );

				var icon_position_class = 'cartflows-pro-elementor__'+settings.icon_position;
				if( '' !== settings.icon.value && position === settings.icon_position ){ #>
					<span class="cartflows-pro-elementor__offer-yes-no-button-icon-wrap {{icon_position_class}}">{{{ iconHTML.value }}}</span>
				<#}
			}
		#>
		<div class="cartflows-pro-elementor__offer-yes-no-button">
			<div class="cartflows-pro-elementor__offer-yes-no-button-wrap">
				<a href="#" class="cartflows-pro-elementor__offer-yes-no-button-link elementor-button elementor-button-link elementor-size-{{ settings.button_size }}">
					<div class="cartflows-elementor__next-step-inner-wrap">
						<# render_icon('before_title_sub_title') #>
							<span class="cartflows-pro-elementor__offer-yes-no-button-content-wrap">
								<div class="cartflows-pro-elementor__offer-yes-no-button-title-wrap">
									<# render_icon('before_title') #>
										<span class="cartflows-pro-elementor__offer-yes-no-button-title">
											{{settings.title}}
										</span>
									<# render_icon('after_title') #>
								</div>
								<# if( '' !== settings.sub_title ){ #>
									<div class="cartflows-pro-elementor__offer-yes-no-button-sub-title">{{settings.sub_title}}</div>
								<# } #>
							</span>
						<# render_icon('after_title_sub_title') #>
					</div>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Offer Yes No Button output in the editor.
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
