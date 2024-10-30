<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
require_once(PSEI_INCLUDES_PATH . 'salesforce.php');

use Productive_Salesforce_Elementor_Integration\Salesforce;


class PSEI_Profile_Page_Widget extends \Elementor\Widget_Base
{

    private $error = '';
    private $salesforce;
    private $records;

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        wp_register_script('script-handle', PSEI_ASSETS_URL . 'js/psei-script.js', array('jquery'), PSEI_PLUGIN_VERSION, true);

        $js_info = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('psei_update_profile_nonce_action')
        );

        wp_localize_script('script-handle', 'js_psei_update_profile', $js_info);
    }

    public function get_script_depends()
    {
        return ['script-handle'];
    }

    public function get_name()
    {
        return 'salesforce-profile';
    }

    public function get_title()
    {
        return __('Profile', 'elemetix');
    }

    public function get_icon()
    {
        return 'eicon-person';
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

        $fieldOptions = $this->PSEIGetTableFields();

        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'elemetix'),
            ]
        );

        $this->add_control(
            'fields',
            [
                'label' => __('Fields', 'elemetix'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $fieldOptions,
            ]
        );

        $this->add_control(
            'login_error_text',
            [
                'label' => __('Not logged In Error Text', 'elemetix'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Please login to view your orders', 'elemetix'),
            ]
        );

        $this->add_control(
            'submit_button',
            [
                'label' => __('Submit Button Text', 'elemetix'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Update', 'elemetix'),
            ]
        );

        $this->end_controls_section();

        // section styling controls

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Section Style', 'elemetix'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_margin',
            [
                'label' => esc_html__('Section Margin', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-profile-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .psei-profile-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .psei-profile-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'section_border',
                'selector' => '{{WRAPPER}} .psei-profile-card',
            ]
        );

        $this->end_controls_section();

        // button styling controls
        $this->start_controls_section(
            'button_style',
            [
                'label' => esc_html__('Button Style', 'elemetix'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
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
                'type' => \Elementor\Controls_Manager::COLOR,
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
                'type' => \Elementor\Controls_Manager::COLOR,
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
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
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
                'type' => \Elementor\Controls_Manager::SLIDER,
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

        // input styling controls
        $this->start_controls_section(
            'section_input_style',
            [
                'label' => esc_html__('Input Style', 'elemetix'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} input',
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_color',
            [
                'label' => esc_html__('Input Color', 'elemetix'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000',
                'selectors' => [
                    '{{WRAPPER}} input' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_background_color',
            [
                'label' => esc_html__('Input Background Color', 'elemetix'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} input' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_width',
            [
                'label' => esc_html__('Input Width', 'elemetix'),
                'type' => \Elementor\Controls_Manager::SLIDER,
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
                    '{{WRAPPER}} input' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_margin',
            [
                'label' => esc_html__('Input Margin', 'elemetix'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} input' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $fields = $settings['fields'];
        $button_label = $settings['submit_button'];
        $login_error_text = $settings['login_error_text'];
        $this->renderContent($fields, $button_label, $login_error_text);
    }

    protected function content_template()
    {
?>
        <# var fields=settings.fields; var button_label=settings.submit_button; var login_error_text=settings.login_error_text; #>

            <?php
            echo '<div class="psei-profile-card psei-wrapper">';
            echo '<div class="psei-card-content">';
            echo    '<div class="psei-update-profile-error"></div>';
            echo    '<form>';
            echo        '<input type="text" name="test1" class="psei-form-control" id="test1" value="' . esc_html('Test input 1') . '" placeholder="' . esc_html__('Test placeholder 1', 'elemetix') . '">';
            echo        '<input type="text" name="test2" class="psei-form-control" id="test2" value="' . esc_html('Test input 2') . '" placeholder="' . esc_html__('Test placeholder 2', 'elemetix') . '">';
            echo        '<input type="text" name="test3" class="psei-form-control" id="test3" value="' . esc_html('Test input 3') . '" placeholder="' . esc_html__('Test placeholder 3', 'elemetix') . '">';
            echo        '<input type="text" name="test4" class="psei-form-control" id="test4" value="' . esc_html('Test input 4') . '" placeholder="' . esc_html__('Test placeholder 4', 'elemetix') . '">';
            echo        '<input type="text" name="test5" class="psei-form-control" id="test5" value="' . esc_html('Test input 5') . '" placeholder="' . esc_html__('Test placeholder 5', 'elemetix') . '">';
            echo        '<input type="text" name="test6" class="psei-form-control" id="test6" value="' . esc_html('Test input 6') . '" placeholder="' . esc_html__('Test placeholder 6', 'elemetix') . '">';

            echo        '<button type="submit" class="psei-btn psei-update-profile-button psei-btn-primary">' . esc_html__('update', 'elemetix') . '</button>';
            echo    '</form>';
            echo '</div>';
            echo '</div>';
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

        protected function PSEIGetTableFields()
        {
            $this->PSEIInitializeSalesforce();
            $table = get_option('psei_salesforce_login_table');
            $results = $this->salesforce->PSEIFetchObjectFields($table);
            $fieldOptions = [];
            if ($results['error']) {
                $this->error = $results['error'];
            } else {
                foreach ($results['data'] as $field) {
                    $fieldOptions[$field->name] = $field->label;
                }
            }
            return $fieldOptions;
        }

        protected function renderContent($fields, $button_label, $login_error_text)
        {
            $results = [];
            if (isset($_SESSION['psei_salesforce_user_id'])) {
                $this->PSEIInitializeSalesforce();
                $results = $this->salesforce->PSEIFetchUserProfileRecords($fields, sanitize_text_field($_SESSION['psei_salesforce_user_id']));
            }
            if ($results) {
                if ($results['error']) {
                    $this->error = $results['error_description'];
                } else {
                    $this->records = $results['data'];
                }
                if ($this->error) {
            ?>
                    <div class="error-message">

                        <?php
                        printf(
                            esc_html__( '%s.', 'elemetix' ),
                            esc_html($this->error)
                      );
                        ?>
                    </div>
                <?php
                } else {
                ?>

                    <?php
                    foreach ($this->records as $record) {
                        // foreach ($fields as $field) {
                    ?>
                        <div class="psei-profile-card psei-wrapper">
                            <div class="psei-profile-header">
                                <div style="display: inline-flex; flex-direction: row; align-items: center;">
                                    <div>
                                        <svg style="width: 50px" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="42" height="42" rx="8" fill="#EAF1FF" />
                                            <path d="M21 20.6618C19.583 20.6618 18.4236 20.2109 17.5218 19.3092C16.62 18.4074 16.1691 17.248 16.1691 15.8309C16.1691 14.4138 16.62 13.2544 17.5218 12.3527C18.4236 11.4509 19.583 11 21 11C22.4171 11 23.5765 11.4509 24.4783 12.3527C25.3801 13.2544 25.831 14.4138 25.831 15.8309C25.831 17.248 25.3801 18.4074 24.4783 19.3092C23.5765 20.2109 22.4171 20.6618 21 20.6618ZM12.6265 31C12.0897 31 11.6334 30.8121 11.2577 30.4364C10.882 30.0607 10.6941 29.6044 10.6941 29.0676V27.9726C10.6941 27.1567 10.8981 26.4589 11.306 25.8792C11.714 25.2995 12.24 24.8594 12.8841 24.5588C14.3226 23.9147 15.7021 23.4316 17.0226 23.1095C18.343 22.7874 19.6689 22.6264 21 22.6264C22.3312 22.6264 23.6517 22.7928 24.9614 23.1256C26.2711 23.4584 27.6452 23.9361 29.0838 24.5588C29.7494 24.8594 30.2861 25.2995 30.6941 25.8792C31.102 26.4589 31.306 27.1567 31.306 27.9726V29.0676C31.306 29.6044 31.1181 30.0607 30.7424 30.4364C30.3667 30.8121 29.9104 31 29.3736 31H12.6265Z" fill="#2D7AFF" />
                                        </svg>
                                    </div>


                                    <div class="psei-profile-name-details">
                                        <span class="psei-profile-user-name psei-profile-text-value"> <?php echo esc_html($record->Name); ?></span><br />
                                        <span class="psei-profile-card-details"><?php echo esc_html($record->Title ? $record->Title : '-'); ?></span>
                                    </div>
                                </div>

                                <button id="psei-profile-btn-edit" class="psei-btn psei-update-profile-btn" style="width: fit-content; height: fit-content;">
                                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.14674 12.8533H1.98767L10.4544 4.38653L9.61347 3.54559L1.14674 12.0123V12.8533ZM12.8817 3.56471L10.4353 1.11834L11.2346 0.319005C11.4535 0.100145 11.7254 -0.00610045 12.0503 0.000270298C12.3752 0.00664105 12.6459 0.118129 12.8626 0.334735L13.6844 1.15656C13.901 1.37317 14.0093 1.64074 14.0093 1.95928C14.0093 2.27781 13.9012 2.54515 13.6851 2.76128L12.8817 3.56471ZM0.579617 14C0.415392 14 0.277733 13.9445 0.16664 13.8335C0.0555465 13.7224 0 13.5848 0 13.4207V11.7842C0 11.7069 0.0127415 11.6365 0.0382245 11.5727C0.0637075 11.509 0.108303 11.4453 0.17201 11.3816L9.63258 1.92105L12.0789 4.36742L2.61838 13.828C2.55467 13.8917 2.48958 13.9363 2.42311 13.9618C2.35662 13.9873 2.28793 14 2.21702 14H0.579617ZM10.0339 3.96606L9.61347 3.54559L10.4544 4.38653L10.0339 3.96606Z" fill="white" />
                                    </svg>
                                    Edit Details
                                </button>

                                <button id="psei-profile-btn-cancel-edit" class="psei-btn psei-grey-btn psei-update-profile-btn" style="width: fit-content; height: fit-content; display: none;">
                                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.14674 12.8533H1.98767L10.4544 4.38653L9.61347 3.54559L1.14674 12.0123V12.8533ZM12.8817 3.56471L10.4353 1.11834L11.2346 0.319005C11.4535 0.100145 11.7254 -0.00610045 12.0503 0.000270298C12.3752 0.00664105 12.6459 0.118129 12.8626 0.334735L13.6844 1.15656C13.901 1.37317 14.0093 1.64074 14.0093 1.95928C14.0093 2.27781 13.9012 2.54515 13.6851 2.76128L12.8817 3.56471ZM0.579617 14C0.415392 14 0.277733 13.9445 0.16664 13.8335C0.0555465 13.7224 0 13.5848 0 13.4207V11.7842C0 11.7069 0.0127415 11.6365 0.0382245 11.5727C0.0637075 11.509 0.108303 11.4453 0.17201 11.3816L9.63258 1.92105L12.0789 4.36742L2.61838 13.828C2.55467 13.8917 2.48958 13.9363 2.42311 13.9618C2.35662 13.9873 2.28793 14 2.21702 14H0.579617ZM10.0339 3.96606L9.61347 3.54559L10.4544 4.38653L10.0339 3.96606Z" fill="white" />
                                    </svg>
                                    Cancel Editing
                                </button>
                            </div>

                            <div class="psei-card-content centered">
                                <div class="psei-update-profile-error" style="display: none;"></div>
                                <form id="psei-profile-edit-info" style="display: none;">
                                    <h6>Edit Your Profile</h6>
                                    <div class="psei-profile-input-section">
                                        <?php
                                        foreach ($fields as $field) {
                                        ?>
                                            <div class="psei-profile-input-group">
                                                <label class="psei-profile-input-label" for="<?php echo esc_attr($field) ?>"><?php echo esc_html($field) ?></label>
                                                <input type="text" name="<?php echo esc_attr($field) ?>" class="psei-form-control psei-profile-input" id="<?php echo esc_attr($field); ?>" value="<?php echo esc_html($record->$field); ?>" placeholder="Enter <?php echo esc_html($field); ?>">
                                                <input style="display: none;" type="text" name="original-<?php echo esc_attr($field) ?>" class="psei-form-control psei-profile-input" id="original-<?php echo esc_attr($field); ?>" value="<?php echo esc_html($record->$field); ?>" placeholder="<?php printf(
                                                    esc_html__( 'Enter %s.', 'elemetix' ),
                                                    esc_html($field)
                                                ) ?>">
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <button type="submit" class="psei-btn psei-update-profile-button psei-btn-primary"><?php printf(
                                        esc_html__( '%s.', 'elemetix' ),
                                        esc_html($button_label)
                                    )?></button>
                                </form>

                                <div id="psei-profile-view-info" style="display: block;">
                                    <h6><?php esc_html__('Personal Information', 'elemetix') ?></h6>
                                    <div class="psei-profile-input-section">
                                        <?php
                                        foreach ($fields as $field) {
                                        ?>
                                            <div class="psei-profile-input-group">
                                                <div style="display: inline-flex; flex-direction: row; align-items: center;">
                                                    <div>
                                                        <svg style="width: 50px" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <rect width="42" height="42" rx="8" fill="#EAF1FF" />
                                                            <path d="M21 20.6618C19.583 20.6618 18.4236 20.2109 17.5218 19.3092C16.62 18.4074 16.1691 17.248 16.1691 15.8309C16.1691 14.4138 16.62 13.2544 17.5218 12.3527C18.4236 11.4509 19.583 11 21 11C22.4171 11 23.5765 11.4509 24.4783 12.3527C25.3801 13.2544 25.831 14.4138 25.831 15.8309C25.831 17.248 25.3801 18.4074 24.4783 19.3092C23.5765 20.2109 22.4171 20.6618 21 20.6618ZM12.6265 31C12.0897 31 11.6334 30.8121 11.2577 30.4364C10.882 30.0607 10.6941 29.6044 10.6941 29.0676V27.9726C10.6941 27.1567 10.8981 26.4589 11.306 25.8792C11.714 25.2995 12.24 24.8594 12.8841 24.5588C14.3226 23.9147 15.7021 23.4316 17.0226 23.1095C18.343 22.7874 19.6689 22.6264 21 22.6264C22.3312 22.6264 23.6517 22.7928 24.9614 23.1256C26.2711 23.4584 27.6452 23.9361 29.0838 24.5588C29.7494 24.8594 30.2861 25.2995 30.6941 25.8792C31.102 26.4589 31.306 27.1567 31.306 27.9726V29.0676C31.306 29.6044 31.1181 30.0607 30.7424 30.4364C30.3667 30.8121 29.9104 31 29.3736 31H12.6265Z" fill="#2D7AFF" />
                                                        </svg>
                                                    </div>


                                                    <div class="psei-profile-name-details">
                                                        <span class="psei-profile-text-value"><?php echo esc_html($record->$field ? $record->$field : '-') ?></span><br/>
                                                        <label class="psei-profile-input-label psei-profile-text-label" for="<?php echo esc_attr($field) ?>"><?php echo esc_html($field) ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                }
            } else {
                ?>
                <div class="error-message">
                    <?php echo esc_html($login_error_text); ?>
                </div>
    <?php
            }
        }
    }