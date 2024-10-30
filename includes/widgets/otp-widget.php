<?php

if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

class PSEI_OTP_Widget extends Widget_Base {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        wp_register_script('psei-otp-script', PSEI_ASSETS_URL . 'js/psei-otp-script.js', array('jquery'), PSEI_PLUGIN_VERSION, true);

        $js_info = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('psei_otp_nonce_action')
        );

        wp_localize_script('psei-otp-script', 'js_psei_otp_verify', $js_info);

    }

    public function get_script_depends()
    {
        
        return ['psei-otp-script', 'send-otp-handler'];
    }


    public function get_name() {
        return 'otp';
    }

    public function get_title()
    {
        return __('OTP', 'elemetix');
    }

    public function get_icon()
    {
        return 'eicon-tel-field';
    }

    public function get_categories()
    {
        return ['esei-salesforce'];
    }

    public function get_keywords()
    {
        return ['salesforce', 'sfcc'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Salesforce OTP Settings', 'elemetix'),
                'tab' => Controls_Manager::TAB_CONTENT,

            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'elemetix'),
                'type' => Controls_Manager::TEXT,
                'default' => __('OTP Verification', 'elemetix'),
            ]
        );

        $this->add_control(
            'sub_title',
            [
                'label' => __('Subtitle', 'elemetix'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Enter code sent through SMS', 'elemetix'),
            ]
        );

        $this->add_control(
            'otp_code',
            [
                'label' => __('OTP Input Label', 'elemetix'),
                'type' => Controls_Manager::TEXT,
                'default' => __('OTP Code', 'elemetix'),
            ]
        );

        $this->add_control(
            'otp_button',
            [
                'label' => __('Login Button Text', 'elemetix'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Verify OTP', 'elemetix'),
            ]
        );

        $this->add_control(
            'otp_send_type',
            [
                'label' => __('Send OTP Type', 'elemetix'),
                'type' => Controls_Manager::SELECT,
                'default' => 'sms',
                'options' => [
                    'sms' => __('SMS', 'elemetix'),
                    'email' => __('Email', 'elemetix'),
                ],
                'frontend_available' => true,
            ]
        );
 
        $this->add_control(
            'next_page',
            [
                'label' => __('Next Page', 'elemetix'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->PSEIGetPages(),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'text_section_style',
            [
                'label' => esc_html__('Text Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Title Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666',
                'selectors' => [
                    '{{WRAPPER}} .psei-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'sub_title_color',
            [
                'label' => esc_html__('Sub Title Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666',
                'selectors' => [
                    '{{WRAPPER}} .psei-sub-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Section Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_margin',
            [
                'label' => esc_html__('Section Margin', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_padding',
            [
                'label' => esc_html__('Section Padding', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'section_background',
                'label' => esc_html__('Background', 'textdomain'),
                'types' => ['classic', 'image'],
                'selector' => '{{WRAPPER}} .psei-wrapper',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'button_section_style',
            [
                'label' => esc_html__('Button Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => esc_html__('Background', 'elemetix'),
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} button',
            ]
        );

        $this->add_control(
            'color',
            [
                'label' => esc_html__('Button Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => esc_html__('Button Border Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6FA1F2',
                'selectors' => [
                    '{{WRAPPER}} button' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'margin',
            [
                'label' => esc_html__('Button Margin', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
			'button_padding',
			[
				'label' => esc_html__('Button Padding', 'elemetix'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'width',
            [
                'label' => esc_html__('Button Width', 'elemetix'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
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
                    '{{WRAPPER}} button' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_input_style',
            [
                'label' => esc_html__('Input Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} .psei_otp_code',
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .psei_otp_code' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_width',
            [
                'label' => esc_html__('Input Width', 'elemetix'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
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
                    '{{WRAPPER}} .psei_otp_code' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_margin',
            [
                'label' => esc_html__('Input Margin', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei_otp_code' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $otp_code = $settings['otp_code'];
        $button_text = $settings['otp_button'];
        $next_page = $settings['next_page'];
        $title = $settings['title'];
        $sub_title = $settings['sub_title'];
        $otp_send_type = $settings['otp_send_type'];

        echo '<div class="psei-otp psei-wrapper psei-card-img-background centered">';
        echo '
		<svg width="31" height="39" viewBox="0 0 31 39" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3.38471 39C2.59818 39 1.92076 38.7159 1.35246 38.1476C0.784194 37.5793 0.500061 36.9019 0.500061 36.1154V16.1078C0.500061 15.3114 0.784194 14.6315 1.35246 14.0682C1.92076 13.5049 2.59818 13.2232 3.38471 13.2232H6.86546V8.63479C6.86546 6.23846 7.70611 4.20029 9.38741 2.52029C11.0687 0.840261 13.1084 0.000244141 15.5066 0.000244141C17.9047 0.000244141 19.9423 0.840261 21.6192 2.52029C23.2961 4.20029 24.1346 6.23846 24.1346 8.63479V13.2232H27.6153C28.4018 13.2232 29.0793 13.5049 29.6476 14.0682C30.2158 14.6315 30.5 15.3114 30.5 16.1078V36.1154C30.5 36.9019 30.2158 37.5793 29.6476 38.1476C29.0793 38.7159 28.4018 39 27.6153 39H3.38471ZM3.38471 36.7308H27.6153C27.7948 36.7308 27.9423 36.6731 28.0577 36.5577C28.1731 36.4423 28.2308 36.2949 28.2308 36.1154V16.1078C28.2308 15.9283 28.1731 15.7809 28.0577 15.6655C27.9423 15.5501 27.7948 15.4924 27.6153 15.4924H3.38471C3.20521 15.4924 3.05776 15.5501 2.94236 15.6655C2.82696 15.7809 2.76926 15.9283 2.76926 16.1078V36.1154C2.76926 36.2949 2.82696 36.4423 2.94236 36.5577C3.05776 36.6731 3.20521 36.7308 3.38471 36.7308ZM15.5084 29.5C16.4413 29.5 17.2378 29.1745 17.8981 28.5235C18.5583 27.8725 18.8885 27.089 18.8885 26.1731C18.8885 25.2885 18.5555 24.4924 17.8897 23.7847C17.2238 23.077 16.4244 22.7232 15.4916 22.7232C14.5587 22.7232 13.7622 23.077 13.102 23.7847C12.4417 24.4924 12.1116 25.2969 12.1116 26.1981C12.1116 27.0994 12.4445 27.8751 13.1104 28.525C13.7762 29.175 14.5756 29.5 15.5084 29.5ZM9.13466 13.2232H21.8654V8.63479C21.8654 6.86663 21.2472 5.3637 20.0109 4.126C18.7745 2.88826 17.2732 2.26939 15.507 2.26939C13.7408 2.26939 12.2372 2.88826 10.9962 4.126C9.75516 5.3637 9.13466 6.86663 9.13466 8.63479V13.2232Z" fill="#2D7AFF"/>
        </svg>
		';
        echo '<h2 class="psei-title">';
        printf(
            esc_html__('%s'),
            esc_html($title)
        );
        echo '</h2>';
        echo '<p class="psei-sub-title">'; 
        printf(
            esc_html__( '%s', 'elemetix' ),
            esc_html($sub_title)
        );
        echo '</p>';
        echo '<form action="" method="post" >';
        echo '<div class="psei-otp-error  error-message hidden"></div>';
        // echo '<input type="text" class="psei_otp_code" name="otp_code" placeholder="' . $otp_code . '">';
        echo '<input type="hidden" name="next_page" value="' . esc_html($next_page) . '">';
        echo '<input type="hidden" name="otp_send_type" value="' . esc_html($otp_send_type) . '"><br/>';
        echo '
        <div class="digit-group" data-group-name="digits" data-autosubmit="false" autocomplete="off">
            <input class="otp-input" placeholder="*" type="text" id="digit-1" name="digit-1" data-next="digit-2" />
            <input class="otp-input" placeholder="*" type="text" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" />
            <input class="otp-input" placeholder="*" type="text" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" />
            <input class="otp-input" placeholder="*" type="text" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" />
            <input class="otp-input" placeholder="*" type="text" id="digit-5" name="digit-5" data-next="digit-6" data-previous="digit-4" />
            <input class="otp-input" placeholder="*" type="text" id="digit-6" name="digit-6" data-previous="digit-5" />
        </div>
        <br/>
        ';
        echo '<button type="submit" class="psei-otp-button psei-btn" name="submit-otp">';
         printf(
            esc_html__( '%s', 'elemetix' ),
            esc_html($button_text)
         );
         echo '</button>';
        echo '</form>';
        echo '</div>';
    }

    protected function PSEIGetPages()
    {
        $pages = get_pages();
        $options = [];
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        return $options;
    }
}
