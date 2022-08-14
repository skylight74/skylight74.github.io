<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
/**
 * RyanCV Skills Widget.
 *
 * @since 1.0
 */

class RyanCV_Skills_Widget extends Widget_Base {

	public function get_name() {
		return 'ryancv-skills';
	}

	public function get_title() {
		return esc_html__( 'Skills', 'ryancv-plugin' );
	}

	public function get_icon() {
		return 'fas fa-dumbbell';
	}

	public function get_categories() {
		return [ 'ryancv-category' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'heading_tab',
			[
				'label' => esc_html__( 'Title', 'ryancv-plugin' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'ryancv-plugin' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter title', 'ryancv-plugin' ),
				'default'     => esc_html__( 'Title', 'ryancv-plugin' ),
			]
		);

		$this->add_control(
			'title_icon',
			[
				'label'       => esc_html__( 'Title Icon', 'ryancv-plugin' ),
				'type'        => Controls_Manager::ICON,
			]
		);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'content_tab',
			[
				'label' => esc_html__( 'Content', 'ryancv-plugin' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'type',
			[
				'label'       => esc_html__( 'Skills Type', 'ryancv-plugin' ),
				'type'        => Controls_Manager::SELECT,
				'default' => 'percent',
				'options' => [
					'percent'  => __( 'Percent', 'ryancv-plugin' ),
					'dotted' => __( 'Dotted', 'ryancv-plugin' ),
					'circles' => __( 'Circles', 'ryancv-plugin' ),
					'list' => __( 'Knowledges', 'ryancv-plugin' ),
				],
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'label', [
				'label'       => esc_html__( 'Label', 'ryancv-plugin' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter label', 'ryancv-plugin' ),
				'default' => esc_html__( 'Enter label', 'ryancv-plugin' ),
			]
		);

		$repeater->add_control(
			'progress', [
				'label'       => esc_html__( 'Progress (in %)', 'ryancv-plugin' ),
				'type'        => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'default' => 99,
			]
		);

		$this->add_control(
			'items',
			[
				'label' => esc_html__( 'Items', 'ryancv-plugin' ),
				'type' => Controls_Manager::REPEATER,
				'prevent_empty' => false,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'title_styling',
			[
				'label'     => esc_html__( 'Title', 'ryancv-plugin' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'ryancv-plugin' ),
				'type'      => Controls_Manager::COLOR,
				'default'	=> '',
				'selectors' => [
					'{{WRAPPER}} .skill-title .name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .skill-title .name',
			]
		);

		$this->add_control(
			'title_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'ryancv-plugin' ),
				'type'      => Controls_Manager::COLOR,
				'default'	=> '',
				'selectors' => [
					'{{WRAPPER}} .skill-title .icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_styling',
			[
				'label' => esc_html__( 'Items', 'ryancv-plugin' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'items_label_color',
			[
				'label' => esc_html__( 'Label Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list ul li .name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'items_label_typography',
				'label' => esc_html__( 'Label Typography:', 'ryancv-plugin' ),
				'selector' => '{{WRAPPER}} .skills-list ul li .name',
			]
		);

		$this->add_control(
			'items_pline_color',
			[
				'label' => esc_html__( 'Progress Line Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list ul li .progress' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_pline2_color',
			[
				'label' => esc_html__( 'Progress Line Active Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list ul li .progress .percentage' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_dg_color',
			[
				'label' => esc_html__( 'Dotted Circles Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list.dotted ul li .progress .dg span' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_da_color',
			[
				'label' => esc_html__( 'Dotted Circles Active Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list.dotted ul li .progress .da span' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_circle_color',
			[
				'label' => esc_html__( 'Circle Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list.circles .progress' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_circle2_color',
			[
				'label' => esc_html__( 'Circle Active Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list.circles .progress .bar' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_list_icon_color',
			[
				'label' => esc_html__( 'List Icon Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .skills-list.list ul li .name:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0
	 */
	protected function render() { 
		$settings = $this->get_settings_for_display();

		$this->add_inline_editing_attributes( 'title', 'basic' );

		?>
		
		<!-- skill item -->
		<div class="skills-item">
			<div class="skills-list <?php if ( $settings['type'] ) : echo esc_attr( $settings['type'] ); endif; ?>">
				<?php if ( $settings['title'] ) : ?>
				<div class="skill-title border-line-h">
					<?php if( $settings['title_icon'] ) : ?>
					<div class="icon"><i class="<?php echo esc_attr( $settings['title_icon'] ); ?>"></i></div>
					<?php endif; ?>
					<div class="name">
						<span <?php echo $this->get_render_attribute_string( 'title' ); ?>><?php echo wp_kses_post( $settings['title'] ); ?></span>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $settings['items'] ) : ?>
				<ul>
					<?php foreach ( $settings['items'] as $index => $item ) : 
				    $item_label = $this->get_repeater_setting_key( 'label', 'items', $index );
				    $this->add_inline_editing_attributes( $item_label, 'basic' );
				    ?>
					<li class="border-line-h"> 
						<?php if ( $item['label'] ) : ?>
						<div class="name">
							<span <?php echo $this->get_render_attribute_string( $item_label ); ?>>
								<?php echo wp_kses_post( $item['label'] ); ?>
							</span>
						</div>
						<?php endif; ?>
						<div class="progress <?php if ( $settings['type'] == 'circles' ) : ?>p<?php echo esc_attr( $item['progress'] ); ?><?php endif; ?>">
							<div class="percentage" style="width:<?php echo esc_attr( $item['progress'] ); ?>%;"></div>
							<?php if ( $settings['type'] == 'circles' ) : ?><span><?php echo esc_attr( $item['progress'] ); ?>%</span><?php endif; ?>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
		</div>
		
		<?php 
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<#
		view.addInlineEditingAttributes( 'title', 'basic' );
		#>
        
		<!-- skill item -->
		<div class="skills-item">
			<div class="skills-list<# if ( settings.type ) { #> {{{ settings.type }}} <# } #>">
				<# if ( settings.title ) { #>
				<div class="skill-title border-line-h">
					<# if ( settings.title_icon ) { #>
					<div class="icon"><i class="{{{ settings.title_icon }}}"></i></div>
					<# } #>
					<div class="name">
						<span {{{ view.getRenderAttributeString( 'title' ) }}}>
							{{{ settings.title }}}
						</span>
					</div>
				</div>
				<# } #>

				<# if ( settings.items ) { #>
				<ul>
					<# _.each( settings.items, function( item, index ) {

				    var item_label = view.getRepeaterSettingKey( 'label', 'items', index );
				    view.addInlineEditingAttributes( item_label, 'basic' );

				    #>
					<li class="border-line-h"> 
						<# if ( item.label ) { #>
						<div class="name">
							<span {{{ view.getRenderAttributeString( item_label ) }}}>
								{{{ item.label }}}
							</span>
						</div>
						<# } #>
						<div class="progress <# if ( settings.type == 'circles' ) { #>p{{{ settings.type }}}<# } #>">
							<div class="percentage" style="width:{{{ item.progress }}}%;"></div>
							<# if ( settings.type == 'circles' ) { #>
							<span>{{{ item.progress }}}%</span>
							<# } #>
						</div>
					</li>
					<# }); #>
				</ul>
				<# } #>
			</div>
		</div>

		<?php 
	}
}

Plugin::instance()->widgets_manager->register_widget_type( new RyanCV_Skills_Widget() );