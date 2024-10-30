<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;

use Productive_Salesforce_Elementor_Integration\Salesforce;

class PSEI_Fields_Widget extends Widget_Base {
    private $salesforce;
  
    /**
	 * Get widget name.
	 *
	 * Retrieve list widget name.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return string Widget name.
	 */
    public function get_name() {
        return 'fields-widget';
    }

    /**
	 * Get widget title.
	 *
	 * Retrieve list widget title.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return string Widget title.
	 */
    public function get_title() {
        return esc_html__( 'Fields', 'elemetix' );
    }

    /**
	 * Get widget icon.
	 *
	 * Retrieve list widget icon.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return string Widget icon.
	 */
    public function get_icon() {
        return 'eicon-text-field';
    }

    /**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the list widget belongs to.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return array Widget categories.
	 */
    public function get_categories() {
        return [ 'esei-salesforce' ];
    }

    /**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the list widget belongs to.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return array Widget keywords.
	 */
    public function get_keywords() {
        return [ 'salesforce', 'fields' ];
    }    

    /**
     *  Get Script Depends
     * 
     * Retrieve the scripts for this widget
     * 
     * @since 1.0.4
     * @access public
     * @return array Scripts
     */
    public function get_script_depends() {
        return [ 'psei-fields-widget' ];
    }

    /**
	 * Get custom help URL.
	 *
	 * Retrieve a URL where the user can get more information about the widget.
	 *
	 * @since 1.0.4
	 * @access public
	 * @return string Widget help URL.
	 */
    public function get_custom_help_url() {
        return '#';
    }

    /**
	 * Register PSEI Fields widget controls.
	 *
	 * Add salesforce table and fields select controls to allow the user to customize the widget settings.
	 *
	 * @since 1.0.4
	 * @access protected
	 */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'elemetix' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        /* Table */
        $this->add_control(
            'table',
            [
                'label' => esc_html__( 'Table', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_table_options(),
                'default' => '',
            ]
        );

        /* Field Select */
        $this->add_control(
            'field',
            [
                'label' => esc_html__( 'Field', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => [],
                'condition' => [
                    'table!' => '',
                ],
            ]
        );

        $this->add_control(
            'show_label',
            [
                'label' => esc_html__( 'Show Field Label', 'elemetix' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'elemetix' ),
                'label_off' => esc_html__( 'No', 'elemetix' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__( 'Style', 'elemetix' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => esc_html__( 'Alignment', 'elemetix' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'elemetix' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'elemetix' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'elemetix' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .psei-list' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__( 'Text Color', 'elemetix' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-field-value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .psei-field-value',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .psei-field-value',
            ]
        );

        $this->end_controls_section();
    }

    private function get_table_options() {        
        // Get the Plugin instance
        $plugin = \Productive_Salesforce_Elementor_Integration\Plugin::instance();
        
        // Initialize Salesforce
        $plugin->PSEIInitializeSalesforce();
        
        // Get table options
        $tableOptions = $plugin->PSEIGetTableOptions();
        
        return $tableOptions;
    }

    public function get_table_fields( $table ) {
        $this->PSEIInitializeSalesforce();
        $results = $this->salesforce->PSEIFetchObjectFields( $table );
        $fieldOptions = [];
        if ( isset( $results[ 'error' ] ) ) {
            $this->error = $results[ 'error' ];
        } else {
            foreach ( $results[ 'data' ] as $field ) {
                $fieldOptions[ $field->name ] = $field->label;
            }
        }
        return $fieldOptions;
    }

    protected function PSEIInitializeSalesforce() {
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

    /**
	 * Render PSEI Fields widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.4
	 * @access protected
	 */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $table = $settings['table'];
        $field = $settings['field'];
        $show_label = $settings['show_label'];

        // Check if we're in the Elementor editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if (!$is_editor && !isset($_SESSION['psei_salesforce_user_id'])) {
            // User is not logged in and we're not in the editor
            $login_error_text = esc_html__('You must be logged in to view this content.', 'elemetix');
            echo '<div class="error-message">' . $login_error_text . '</div>';
            return;
        }

        if (!empty($table) && !empty($field)) {
            $this->PSEIInitializeSalesforce();

            if ($is_editor) {
                // In editor, fetch 5 records
                $results = $this->salesforce->PSEIFetchObjectRecords($table, [$field], 5);
            } else {
                // For logged-in users, fetch only their records
                $user_id = sanitize_text_field($_SESSION['psei_salesforce_user_id']);
                $results = $this->salesforce->PSEIFetchUserOrderRecords([$field], $user_id, 1000, $table);
            }

            if (isset($results['error'])) {
                echo esc_html__('Error: ', 'elemetix') . esc_html($results['error_description']);
            } else if (isset($results['data']) && is_array($results['data'])) {
                $fields = $this->get_table_fields($table);
                echo '<div class="fields-wrapper" data-widget-type="fields-widget">';
                
                $empty_field_message = esc_html__('No data available for this field.', 'elemetix');
                $has_data = false;

                foreach ($results['data'] as $record) {
                    if (isset($record->$field) && !empty($record->$field)) {
                        if (!$has_data) {
                            echo '<ul class="psei-list">';
                            $has_data = true;
                        }
                        echo '<li>';
                        if ($show_label === 'yes') {
                            $field_label = isset($fields[$field]) ? $fields[$field] : $field;
                            echo '<div class="psei-field-label">' . esc_html($field_label) . '</div>';
                        }
                        echo '<div class="psei-field-value">' . esc_html($record->$field) . '</div>';
                        echo '</li>';
                    }
                }

                if ($has_data) {
                    echo '</ul>';
                } else {
                    echo '<div class="psei-field-empty">' . $empty_field_message . '</div>';
                }

                echo '</div>';
            } else {
                echo esc_html__('No records found in the table & field.', 'elemetix');
            }
        } else {
            echo esc_html__('Please select both a table and a field from the dropdown.', 'elemetix');
        }
    }
}