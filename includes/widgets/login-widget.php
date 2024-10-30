<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

class PSEI_Login_Widget extends Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'script-handle', PSEI_ASSETS_URL . 'js/psei-script.js', array( 'jquery' ), PSEI_PLUGIN_VERSION, true );

		$js_info = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'psei_login_nonce_action' )
		);

		wp_localize_script( 'script-handle', 'js_psei_login', $js_info );
	}

	public function get_script_depends() {
		return [ 'script-handle' ];
	}

	public function get_name() {
		return 'login';
	}

	public function get_title() {
		return esc_html__( 'Login', 'elemetix' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return [ 'esei-salesforce' ];
	}

	public function get_keywords() {
		return [ 'salesforce', 'sfcc' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Salesforce Login Settings', 'elemetix' ),
				'tab' => Controls_Manager::TAB_CONTENT,

			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'elemetix' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Login', 'elemetix' ),
			]
		);

		$this->add_control(
			'sub_title',
			[
				'label' => esc_html__( 'Subtitle', 'elemetix' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Login to your account', 'elemetix' ),
			]
		);

		$this->add_control(
			'phone_number',
			[
				'label' => esc_html__( 'Phone Number Label', 'elemetix' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Phone number', 'elemetix' ),
			]
		);

		$this->add_control(
			'login_button',
			[
				'label' => esc_html__( 'Login Button Text', 'elemetix' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Login', 'elemetix' ),
			]
		);
		$this->add_control(
			'error_message',
			[
				'label' => esc_html__( 'Error Message', 'elemetix' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Cannot login', 'elemetix' ),
			]
		);

		$this->add_control(
			'next_page',
			[
				'label' => esc_html__( 'Next Page', 'elemetix' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->PSEIGetPages(),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'text_section_style',
			[
				'label' => esc_html__( 'Text Style', 'elemetix' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'elemetix' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666',
				'selectors' => [
					'{{ WRAPPER }} .psei-title' => 'color: {{ VALUE }}',
				],
			]
		);

		$this->add_control(
			'sub_title_color',
			[
				'label' => esc_html__( 'Sub Title Color', 'elemetix' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666',
				'selectors' => [
					'{{ WRAPPER }} .psei-sub-title' => 'color: {{ VALUE }}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Section Style', 'elemetix' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'section_margin',
			[
				'label' => esc_html__( 'Section Margin', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{ WRAPPER }} .psei-wrapper' => 'margin: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'section_padding',
			[
				'label' => esc_html__( 'Section Padding', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{ WRAPPER }} .psei-wrapper' => 'padding: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'section_background',
				'label' => esc_html__( 'Background', 'elemetix' ),
				'types' => [ 'classic', 'image' ],
				'selector' => '{{ WRAPPER }} .psei-wrapper',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'button_style',
			[
				'label' => esc_html__( 'Button Style', 'elemetix' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'label' => esc_html__( 'Background', 'elemetix' ),
				'types' => [ 'classic', 'gradient', ],
				'selector' => '{{ WRAPPER }} button',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => esc_html__( 'Button Color', 'elemetix' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#fff',
				'selectors' => [
					'{{ WRAPPER }} button' => 'color: {{ VALUE }}',
				],
			]
		);      

		$this->add_control(
			'border_color',
			[
				'label' => esc_html__( 'Button Border Color', 'elemetix' ),
				'type' =>  Controls_Manager::COLOR,
				'default' => '#6FA1F2',
				'selectors' => [
					'{{ WRAPPER }} button' => 'border-color: {{ VALUE }}',
				],
			]
		);

		$this->add_control(
			'margin',
			[
				'label' => esc_html__( 'Button Margin', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{ WRAPPER }} button' => 'margin: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'padding',
			[
				'label' => esc_html__( 'Button Padding', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{ WRAPPER }} button' => 'padding: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => esc_html__( 'Button Border Radius', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{ WRAPPER }} button' => 'border-radius: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT } {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'width',
			[
				'label' => esc_html__( 'Button Width', 'elemetix' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{ WRAPPER }} button' => 'width: {{ SIZE }}{{ UNIT }};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_input_style',
			[
				'label' => esc_html__( 'Input Style', 'elemetix' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{ WRAPPER }} .psei_phone_number',
			]
		);

		$this->add_control(
			'input_border_radius',
			[
				'label' => esc_html__( 'Button Border Radius', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{ WRAPPER }} .psei_phone_number' => 'border-radius: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'input_width',
			[
				'label' => esc_html__( 'Input Width', 'elemetix' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{ WRAPPER }} .psei_phone_number' => 'width: {{ SIZE }}{{ UNIT }};',
				],
			]
		);

		$this->add_control(
			'input_margin',
			[
				'label' => esc_html__( 'Input Margin', 'elemetix' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{ WRAPPER }} .psei_phone_number' => 'margin: {{ TOP }}{{ UNIT }} {{ RIGHT }}{{ UNIT }} {{ BOTTOM }}{{ UNIT }} {{ LEFT }}{{ UNIT }};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$phone_number = $settings[ 'phone_number' ];
		$next_page = $settings[ 'next_page' ];
		$login_button = $settings[ 'login_button' ];
		$title = $settings[ 'title' ];
		$sub_title = $settings[ 'sub_title' ];
		$error_message = $settings['error_message'];

		echo '<div class="psei psei-login psei-wrapper psei-card-img-background centered">';
		echo '
		<svg width="43" height="42" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="0.5" width="42" height="42" rx="8" fill="#EAF1FF"/>
			<path d="M21.5 20.6618C20.083 20.6618 18.9236 20.2109 18.0218 19.3092C17.12 18.4074 16.6691 17.248 16.6691 15.8309C16.6691 14.4138 17.12 13.2544 18.0218 12.3527C18.9236 11.4509 20.083 11 21.5 11C22.9171 11 24.0765 11.4509 24.9783 12.3527C25.8801 13.2544 26.331 14.4138 26.331 15.8309C26.331 17.248 25.8801 18.4074 24.9783 19.3092C24.0765 20.2109 22.9171 20.6618 21.5 20.6618ZM13.1265 31C12.5897 31 12.1334 30.8121 11.7577 30.4364C11.382 30.0607 11.1941 29.6044 11.1941 29.0676V27.9726C11.1941 27.1567 11.3981 26.4589 11.806 25.8792C12.214 25.2995 12.74 24.8594 13.3841 24.5588C14.8226 23.9147 16.2021 23.4316 17.5226 23.1095C18.843 22.7874 20.1689 22.6264 21.5 22.6264C22.8312 22.6264 24.1517 22.7928 25.4614 23.1256C26.7711 23.4584 28.1452 23.9361 29.5838 24.5588C30.2494 24.8594 30.7861 25.2995 31.1941 25.8792C31.602 26.4589 31.806 27.1567 31.806 27.9726V29.0676C31.806 29.6044 31.6181 30.0607 31.2424 30.4364C30.8667 30.8121 30.4104 31 29.8736 31H13.1265Z" fill="#2D7AFF"/>
		</svg>
		';
		echo '<h2 class="psei psei-title">';
		printf(
			esc_html__( '%s', 'elemetix' ),
			esc_html( $title )
	  	);
		echo '</h2>';
		echo '<p class="psei psei-sub-title">';
		printf(
			esc_html__( '%s', 'elemetix' ),
			esc_html( $sub_title )
	  	);
		echo '</p>';
		echo '<form class="psei psei_login_form" action="" method="post">';
		echo '<div class="psei psei-login-error error-message hidden"></div>';
		echo '<label for="phone_number" class="psei psei-form-element psei-label">';
		printf(
			esc_html__( '%s', 'elemetix' ),
			esc_html( $phone_number )
	  	);
		echo '</label><br/>';
		echo '<input type="number" class="psei psei-form-element psei_phone_number psei_input" name="phone_number" placeholder="Enter '; 
		printf(
			esc_html__( '%s', 'elemetix' ),
			esc_html( $phone_number )
	  	);
		echo '"><br/><br/>';
		echo '<input type="hidden" name="next_page" value="' . esc_html( $next_page ) . '">';
		echo '<input type="hidden" name="error_message" value="' . esc_html( $error_message ) . '">';
		echo '<button type="submit" class="psei psei-form-element psei-login-button psei-btn" name="login">';
		printf(
			esc_html__( '%s', 'elemetix' ),
			esc_html( $login_button )
	  	);
		echo '</button><br/>';
		echo '</form>';
		echo '</div>';
	}

	protected function content_template() {
?>
		<div class="psei-login psei-wrapper">
		<svg width="43" height="42" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="0.5" width="42" height="42" rx="8" fill="#EAF1FF"/>
			<path d="M21.5 20.6618C20.083 20.6618 18.9236 20.2109 18.0218 19.3092C17.12 18.4074 16.6691 17.248 16.6691 15.8309C16.6691 14.4138 17.12 13.2544 18.0218 12.3527C18.9236 11.4509 20.083 11 21.5 11C22.9171 11 24.0765 11.4509 24.9783 12.3527C25.8801 13.2544 26.331 14.4138 26.331 15.8309C26.331 17.248 25.8801 18.4074 24.9783 19.3092C24.0765 20.2109 22.9171 20.6618 21.5 20.6618ZM13.1265 31C12.5897 31 12.1334 30.8121 11.7577 30.4364C11.382 30.0607 11.1941 29.6044 11.1941 29.0676V27.9726C11.1941 27.1567 11.3981 26.4589 11.806 25.8792C12.214 25.2995 12.74 24.8594 13.3841 24.5588C14.8226 23.9147 16.2021 23.4316 17.5226 23.1095C18.843 22.7874 20.1689 22.6264 21.5 22.6264C22.8312 22.6264 24.1517 22.7928 25.4614 23.1256C26.7711 23.4584 28.1452 23.9361 29.5838 24.5588C30.2494 24.8594 30.7861 25.2995 31.1941 25.8792C31.602 26.4589 31.806 27.1567 31.806 27.9726V29.0676C31.806 29.6044 31.6181 30.0607 31.2424 30.4364C30.8667 30.8121 30.4104 31 29.8736 31H13.1265Z" fill="#2D7AFF"/>
		</svg>

			<h2 class="psei-title">{{{ settings.title }}}</h2>
			<p class="psei-sub-title">{{{ settings.sub_title }}}</p>
			<form id="psei_login_form" action="" method="post">
				<div class="psei-login-error"></div>
				<input type="email" class="psei_phone_number psei_input" name="phone_number" placeholder="Enter {{ settings.phone_number }}"><br/><br/>
				<input type="hidden" name="next_page" value="{{ settings.next_page }}">
				<button type="submit" class="psei-login-button psei-btn" name="login">{{ settings.login_button }}</button>
			</form>
		</div>

	<?php
	}

	protected function PSEIGetPages() {
		$pages = get_pages();
		$options = [];
		foreach ( $pages as $page ) {
			$options[ $page->ID ] = $page->post_title;
		}
		return $options;
	}
}
