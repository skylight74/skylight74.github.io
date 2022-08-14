<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
/**
 * RyanCV Skills Widget.
 *
 * @since 1.0
 */

class RyanCV_Resume_Widget extends Widget_Base {

	public function get_name() {
		return 'ryancv-resume';
	}

	public function get_title() {
		return esc_html__( 'Resume', 'ryancv-plugin' );
	}

	public function get_icon() {
		return 'far fa-address-card';
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'active', [
				'label' => esc_html__( 'Active', 'ryancv-plugin' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'ryancv-plugin' ),
				'label_off' => esc_html__( 'No', 'ryancv-plugin' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$repeater->add_control(
			'image', [
				'label' => esc_html__( 'Image', 'ryancv-plugin' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => '',
				],
			]
		);

		$repeater->add_control(
			'years', [
				'label'       => esc_html__( 'Years', 'ryancv-plugin' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter years', 'ryancv-plugin' ),
				'default' => esc_html__( 'Enter years', 'ryancv-plugin' ),
			]
		);

		$repeater->add_control(
			'title', [
				'label'       => esc_html__( 'Title', 'ryancv-plugin' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter title', 'ryancv-plugin' ),
				'default' => esc_html__( 'Enter title', 'ryancv-plugin' ),
			]
		);

		$repeater->add_control(
			'subtitle', [
				'label'       => esc_html__( 'Subtitle', 'ryancv-plugin' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter subtitle', 'ryancv-plugin' ),
				'default' => esc_html__( 'Enter subtitle', 'ryancv-plugin' ),
			]
		);

		$repeater->add_control(
			'text', [
				'label'       => esc_html__( 'Text', 'ryancv-plugin' ),
				'type'        => Controls_Manager::WYSIWYG,
				'placeholder' => esc_html__( 'Enter text', 'ryancv-plugin' ),
				'default' => esc_html__( 'Enter text', 'ryancv-plugin' ),
			]
		);

		$this->add_control(
			'items',
			[
				'label' => esc_html__( 'Items', 'ryancv-plugin' ),
				'type' => Controls_Manager::REPEATER,
				'prevent_empty' => false,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
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
					'{{WRAPPER}} .resume-title .name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .resume-title .name',
			]
		);

		$this->add_control(
			'title_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'ryancv-plugin' ),
				'type'      => Controls_Manager::COLOR,
				'default'	=> '',
				'selectors' => [
					'{{WRAPPER}} .resume-title .icon' => 'color: {{VALUE}};',
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
			'items_date_color',
			[
				'label' => esc_html__( 'Date Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .resume-items .resume-item .date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'items_date2_color',
			[
				'label' => esc_html__( 'Date Active Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .resume-items .resume-item.active .date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'items_date_typography',
				'label' => esc_html__( 'Date Typography:', 'ryancv-plugin' ),
				'selector' => '{{WRAPPER}} .resume-items .resume-item .date',
			]
		);

		$this->add_control(
			'items_title_color',
			[
				'label' => esc_html__( 'Title Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .resume-items .resume-item .name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'items_title_typography',
				'label' => esc_html__( 'Title Typography:', 'ryancv-plugin' ),
				'selector' => '{{WRAPPER}} .resume-items .resume-item .name',
			]
		);

		$this->add_control(
			'items_subtitle_color',
			[
				'label' => esc_html__( 'Subtitle Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .resume-items .resume-item .company' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'items_subtitle_typography',
				'label' => esc_html__( 'Subtitle Typography:', 'ryancv-plugin' ),
				'selector' => '{{WRAPPER}} .resume-items .resume-item .company',
			]
		);

		$this->add_control(
			'items_text_color',
			[
				'label' => esc_html__( 'Text Color', 'ryancv-plugin' ),
				'type' => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .resume-items .resume-item .single-post-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'items_text_typography',
				'label' => esc_html__( 'Text Typography:', 'ryancv-plugin' ),
				'selector' => '{{WRAPPER}} .resume-items .resume-item .single-post-text',
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
		
		<!-- resume item -->
		<div class="resume-item">
			<?php if ( $settings['title'] ) : ?>
			<div class="resume-title border-line-h">
				<?php if ( $settings['title_icon'] ) : ?>
				<div class="icon"><i class="<?php echo esc_attr( $settings['title_icon'] ); ?>"></i></div>
				<?php endif; ?>
				<div class="name">
					<span <?php echo $this->get_render_attribute_string( 'title' ); ?>><?php echo wp_kses_post( $settings['title'] ); ?></span>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $settings['items'] ) : ?>
			<div class="resume-items">
				<?php foreach ( $settings['items'] as $index => $item ) : 
			    $item_years = $this->get_repeater_setting_key( 'years', 'items', $index );
			    $this->add_inline_editing_attributes( $item_years, 'basic' );
			    $item_title = $this->get_repeater_setting_key( 'title', 'items', $index );
			    $this->add_inline_editing_attributes( $item_title, 'basic' );
			    $item_subtitle = $this->get_repeater_setting_key( 'subtitle', 'items', $index );
			    $this->add_inline_editing_attributes( $item_subtitle, 'basic' );
			    $item_text = $this->get_repeater_setting_key( 'subtitle', 'items', $index );
			    $this->add_inline_editing_attributes( $item_text, 'advanced' );
			    ?>
				<div class="resume-item border-line-h <?php if ( $item['active'] == 'yes' ) : ?>active<?php endif; ?>">
					<?php if ( $item['image'] ) : ?>
					<div class="image">
						<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="" />
					</div>
					<?php endif; ?>
					<?php if ( $item['years'] ) : ?>
					<div class="date">
						<span <?php echo $this->get_render_attribute_string( $item_years ); ?>>
							<?php echo wp_kses_post( $item['years'] ); ?>
						</span>
					</div>
					<?php endif; ?>
					<?php if ( $item['title'] ) : ?>
					<div class="name">
						<span <?php echo $this->get_render_attribute_string( $item_title ); ?>>
							<?php echo wp_kses_post( $item['title'] ); ?>
						</span>
					</div>
					<?php endif; ?>
					<?php if ( $item['subtitle'] ) : ?>
					<div class="company">
						<span <?php echo $this->get_render_attribute_string( $item_subtitle ); ?>>
							<?php echo wp_kses_post( $item['subtitle'] ); ?>
						</span>
					</div>
					<?php endif; ?>
					<?php if ( $item['text'] ) : ?>
					<div class="single-post-text">
						<div <?php echo $this->get_render_attribute_string( $item_text ); ?>>
							<?php echo wp_kses_post( $item['text'] ); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
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
        
		<!-- resume item -->
		<div class="resume-item">
			<# if ( settings.title ) { #>
			<div class="resume-title border-line-h">
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
			<div class="resume-items">
				<# _.each( settings.items, function( item, index ) {

			    var item_years = view.getRepeaterSettingKey( 'years', 'items', index );
			    view.addInlineEditingAttributes( item_years, 'basic' );
			    var item_title = view.getRepeaterSettingKey( 'title', 'items', index );
			    view.addInlineEditingAttributes( item_title, 'basic' );
			    var item_subtitle = view.getRepeaterSettingKey( 'subtitle', 'items', index );
			    view.addInlineEditingAttributes( item_subtitle, 'basic' );
			    var item_text = view.getRepeaterSettingKey( 'text', 'items', index );
			    view.addInlineEditingAttributes( item_text, 'advanced' );

			    #>
				<div class="resume-item border-line-h <# if ( item.active == 'yes' ) { #>active<# } #>">
					<# if ( item.image ) { #>
					<div class="image">
						<img src="{{{ item.image.url }}}" alt="" />
					</div>
					<# } #>
					<# if ( item.years ) { #>
					<div class="date">
						<span {{{ view.getRenderAttributeString( item_years ) }}}>
							{{{ item.years }}}
						</span>
					</div>
					<# } #>
					<# if ( item.title ) { #>
					<div class="name">
						<span {{{ view.getRenderAttributeString( item_title ) }}}>
							{{{ item.title }}}
						</span>
					</div>
					<# } #>
					<# if ( item.subtitle ) { #>
					<div class="company">
						<span {{{ view.getRenderAttributeString( item_subtitle ) }}}>
							{{{ item.subtitle }}}
						</span>
					</div>
					<# } #>
					<# if ( item.text ) { #>
					<div class="single-post-text">
						<div {{{ view.getRenderAttributeString( item_text ) }}}>
							{{{ item.text }}}
						</div>
					</div>
					<# } #>
				</div>
				<# }); #>
			</div>
			<# } #>
		</div>

		<?php 
	}
}

Plugin::instance()->widgets_manager->register_widget_type( new RyanCV_Resume_Widget() );