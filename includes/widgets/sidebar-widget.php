<?php

if (!defined('ABSPATH')) exit;
use Productive_Salesforce_Elementor_Integration\Salesforce;

class PSEI_Sidebar_Widget extends \Elementor\Widget_Base
{

    private $salesforce;

    public function get_name()
    {
        return 'sidebar-widget';
    }

    public function get_title()
    {
        return __('Sidebar', 'elemetix');
    }

    public function get_icon()
    {
        return 'eicon-sidebar';
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
                'label' => __('Content', 'elemetix'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'list',
            [
                'label' => esc_html__('Menu Items', 'elemetix'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'text',
                        'label' => esc_html__('Text', 'elemetix'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => esc_html__('List Item', 'elemetix'),
                        'default' => esc_html__('List Item', 'elemetix'),
                    ],
                    [
                        'name' => 'icon',
                        'label' => esc_html__('Icon', 'elemetix'),
                        'type' => \Elementor\Controls_Manager::ICONS,
                        'default' => [
                            'value' => 'fas fa-star',
                            'library' => 'solid',
                        ],
                    ],
                    [
                        'name' => 'page',
                        'label' => __('Link Page', 'elemetix'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => $this->PSEIGetPages(),
                    ]
                ],
                'default' => [
                    [
                        'text' => esc_html__('Dashboard', 'elemetix'),
                        'icon' => 'fas fa-star',
                    ]
                ],
                'title_field' => '{{{ text }}}',
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Section Style', 'elemetix'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => esc_html__('Background', 'elemetix'),
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .sfad-widget',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'elemetix'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000',
                'selectors' => [
                    '{{WRAPPER}} .sfad-widget-text' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .sfad-sidemenu-text' => 'color: {{VALUE}}'
                ],
            ]
        );

        $this->add_control(
            'section_margin',
            [
                'label' => esc_html__('Section Margin', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .sfad-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_padding',
            [
                'label' => esc_html__('Section Padding', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .sfad-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'item_padding',
            [
                'label' => esc_html__('Item Padding', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-sidebar-list-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_margin',
            [
                'label' => esc_html__('Item Margin', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-sidebar-list-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $user_profile = json_decode(sanitize_text_field($_SESSION['user_profile']));
     

?>
        <div class="sfad-widget sfad-wrapper psei-sidebar-wrapper" style="padding: 25px;">
            <div class="sfad-profile-header">
                <div style="display: inline-flex; flex-direction: row; align-items: center;">
                    <div>
                        <svg style="width: 50px" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="42" height="42" rx="8" fill="#EAF1FF" />
                            <path d="M21 20.6618C19.583 20.6618 18.4236 20.2109 17.5218 19.3092C16.62 18.4074 16.1691 17.248 16.1691 15.8309C16.1691 14.4138 16.62 13.2544 17.5218 12.3527C18.4236 11.4509 19.583 11 21 11C22.4171 11 23.5765 11.4509 24.4783 12.3527C25.3801 13.2544 25.831 14.4138 25.831 15.8309C25.831 17.248 25.3801 18.4074 24.4783 19.3092C23.5765 20.2109 22.4171 20.6618 21 20.6618ZM12.6265 31C12.0897 31 11.6334 30.8121 11.2577 30.4364C10.882 30.0607 10.6941 29.6044 10.6941 29.0676V27.9726C10.6941 27.1567 10.8981 26.4589 11.306 25.8792C11.714 25.2995 12.24 24.8594 12.8841 24.5588C14.3226 23.9147 15.7021 23.4316 17.0226 23.1095C18.343 22.7874 19.6689 22.6264 21 22.6264C22.3312 22.6264 23.6517 22.7928 24.9614 23.1256C26.2711 23.4584 27.6452 23.9361 29.0838 24.5588C29.7494 24.8594 30.2861 25.2995 30.6941 25.8792C31.102 26.4589 31.306 27.1567 31.306 27.9726V29.0676C31.306 29.6044 31.1181 30.0607 30.7424 30.4364C30.3667 30.8121 29.9104 31 29.3736 31H12.6265Z" fill="#2D7AFF" />
                        </svg>
                    </div>


                    <div class="sfad-profile-name-details">
                        <span class="sfad-profile-user-name sfad-profile-text-value"><?php echo esc_html($user_profile ? $user_profile[0]->Name : "Elementix"); ?></span><br />
                        <span class="sfad-profile-card-details">ID <?php echo esc_html($user_profile ? $user_profile[0]->Id : ""); ?></span>
                    </div>
                </div>

            </div>
            <?php foreach($settings['sidebar_items'] as $sidebar_item): ?>
                <div class="psei-menu-item-wrapper">
                    <ul class="psei-sidebar-list">
                        <?php foreach ($sidebar_item['list'] as $item) : ?>
                            <li class="psei-sidebar-list-item">
                                <?php
                                if ($item['page']) {
                                    $link_page = get_page_link($item['page']);
                                    $current_page = esc_html((empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://".sanitize_text_field($_SERVER['HTTP_HOST']).sanitize_text_field($_SERVER['REQUEST_URI'])."");
        
        
                                    if ($link_page == $current_page) {
                                ?>
                                        <a class="sfad-widget-text sfad-sidemenu-current-page" href="<?php echo esc_url(get_page_link($item['page'])) ?>">
                                        <?php } else { ?>
                                            <a class="sfad-widget-text" href="<?php echo esc_url(get_page_link($item['page'])) ?>">
                                            <?php } ?>
                                            <div class="psei-sidebar-item-wrapper">
                                                <div class="psei-sidebar-item-name">
                                                    <span class="psei-icon-wrapper">
                                                        <?php \Elementor\Icons_Manager::render_icon($item['icon'], ['aria-hidden' => 'true']); ?>
                                                    </span>
                                                    <p class="sfad-sidemenu-text"><?php echo esc_html($item['text']); ?></p>
                                                </div>
                                                <span class="psei-icon-wrapper">
                                                    <?php \Elementor\Icons_Manager::render_icon([
                                                        'value' => 'fas fa-chevron-right',
                                                        'library' => 'solid',
                                                    ], ['aria-hidden' => 'true']); ?>
                                                </span>
                                            </div>
                                            </a>
                                        <?php
                                    } else {
                                        ?>
                                            <span class="sfad-sidemenu-separator">
                                                <?php echo esc_html($item['text']); ?>
                                            </span>
                                        <?php
                                    }
                                        ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    <?php
    }

    protected function content_template()
    {
    ?>

        <div class="sfad-widget">
            <ul>

            </ul>
        </div>
<?php
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

    protected function PSEIInitializeSalesforce()
        {
            $saved_salesforce_token = get_option('psei_salesforce_token');
            $saved_salesforce_username = get_option('psei_salesforce_username');
            $saved_salesforce_password = get_option('psei_salesforce_password');
            $saved_salesforce_client_id = get_option('psei_salesforce_client_id');
            $saved_salesforce_client_secret = get_option('psei_salesforce_client_secret');
            $saved_salesforce_instance_url = get_option('psei_salesforce_instance_url');
            $saved_salesforce_access_token = get_option('psei_salesforce_access_token');
            $this->salesforce = Salesforce::instance(
                $saved_salesforce_client_id,
                $saved_salesforce_client_secret,
                $saved_salesforce_username,
                $saved_salesforce_password,
                $saved_salesforce_token,
                $saved_salesforce_instance_url,
                $saved_salesforce_access_token
            );
        }
}
