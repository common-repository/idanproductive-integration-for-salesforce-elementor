<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
require_once(PSEI_INCLUDES_PATH . 'salesforce.php');

use Productive_Salesforce_Elementor_Integration\Salesforce;


class PSEI_Orders_Widget extends \Elementor\Widget_Base
{
    private $salesforce;
    private $error;
    private $records;

    public function get_name()
    {
        return 'orders';
    }

    public function get_title()
    {
        return __('Orders - Table View', 'integrate-salesforce-and-elementor');
    }

    public function get_icon()
    {
        return 'eicon-cart';
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

        $tableOptions = $this->PSEIGetTableOptions();

        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'integrate-salesforce-and-elementor'),
            ]
        );

        $this->add_control(
            'table',
            [
                'label' => __('Table', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $tableOptions,
                'default' => 'order_table__c',
            ]
        );

        $control = (array) $this;
        $table = isset( $control["\0Elementor\Controls_Stack\0data"]['settings']['table'] ) 
                        ? $control["\0Elementor\Controls_Stack\0data"]['settings']['table'] 
                        : '';
            
            
        $fieldOptions = [];
        if($table){
            $fieldOptions = $this->PSEIGetTableFields($table);
            $this->add_control(
                'fields',
                [
                    'label' => __( 'Fields', 'integrate-salesforce-and-elementor' ),
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'multiple' => true,
                    'options' => $fieldOptions,
                ]
            );
        }


        $this->add_control(
            'submit_button',
            [
                'label' => __('Submit Button Text', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Update', 'integrate-salesforce-and-elementor'),
            ]
        );     
        
        $this->add_control(
            'error_text',
            [
                'label' => __('No Records Text', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No records found', 'integrate-salesforce-and-elementor'),
            ]
        );

        $this->add_control(
            'login_error_text',
            [
                'label' => __('Not logged In Error Text', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Please login to view your orders', 'integrate-salesforce-and-elementor'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'card_section_style',
            [
                'label' => esc_html__('Card Style', 'integrate-salesforce-and-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_section_margin',
            [
                'label' => esc_html__('Card Margin', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-order-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_padding',
            [
                'label' => esc_html__('Section Padding', 'psintegrate-salesforce-and-elementorei'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .psei-order-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .psei-order-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'section_border',
                'selector' => '{{WRAPPER}} .psei-order-card',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => esc_html__('Background', 'integrate-salesforce-and-elementor'),
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .psei-order-card',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'button_section_style',
            [
                'label' => esc_html__('Button Style', 'integrate-salesforce-and-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background',
                'label' => esc_html__('Background', 'integrate-salesforce-and-elementor'),
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} button',
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => esc_html__('Button Color', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_border_color',
            [
                'label' => esc_html__('Button Border Color', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6FA1F2',
                'selectors' => [
                    '{{WRAPPER}} button' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_margin',
            [
                'label' => esc_html__('Button Margin', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_width',
            [
                'label' => esc_html__('Button Width', 'integrate-salesforce-and-elementor'),
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

        // Text styling controls
        $this->start_controls_section(
            'section_text_style',
            [
                'label' => esc_html__('Text Style', 'pintegrate-salesforce-and-elementorsei'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_wrapper_padding',
            [
                'label' => esc_html__('Text wrapper padding', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} psei-orders-text-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label' => esc_html__('Header Text Color', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000',
                'selectors' => [
                    '{{WRAPPER}} psei-orders-header' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000',
                'selectors' => [
                    '{{WRAPPER}} psei-orders-value' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'text_wrapper_width',
            [
                'label' => esc_html__('Text Wrapper Width', 'integrate-salesforce-and-elementor'),
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
                    '{{WRAPPER}} psei-orders-text-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'text_wrapper_margin',
            [
                'label' => esc_html__('Text Wrapper Margin', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} psei-orders-text-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'text_margin',
            [
                'label' => esc_html__('Text Margin', 'integrate-salesforce-and-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} psei-orders-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
			'text_header_font_size',
            [
                'label' => esc_html__( 'Header Font Size', 'integrate-salesforce-and-elementor' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} psei-orders-header' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
		);

        $this->add_control(
			'text_font_size',
            [
                'label' => esc_html__( 'Text Font Size', 'integrate-salesforce-and-elementor' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} psei-orders-value' => 'font-size: {{SIZE}}{{UNIT}};',
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
        $error_text = $settings['error_text'];
        $login_error_text = $settings['login_error_text'];
        $table = $settings['table'];
        $this->renderContent($fields, $button_label, $error_text, $login_error_text, $table);
    }

    protected function content_template()
    {
       echo '<div class="psei-order-card">';
       echo     '<div class="psei-order-card-content">';
       echo         '<div class="psei-order">';
       echo             '<div class="psei-orders-text-wrapper">';
       echo                 '<h4 class="psei-orders-text psei-orders-header">'. esc_html__('Test title 1', 'psintegrate-salesforce-and-elementorei') .'</h4>';
       echo                 '<h6 class="psei-orders-text psei-orders-value">'. esc_html__('Sample Test 1', 'integrate-salesforce-and-elementor') .'</h6>';
       echo             '</div>';
       echo             '<div class="psei-orders-text-wrapper">';
       echo                 '<h4 class="psei-orders-text psei-orders-header">'. esc_html__('Test title 2', 'integrate-salesforce-and-elementor') .'</h4>';
       echo                 '<h6 class="psei-orders-text psei-orders-value">'. esc_html__('Sample Test 2', 'integrate-salesforce-and-elementor') .'</h6>';
       echo             '</div>';
       echo             '<div class="psei-orders-text-wrapper">';
       echo                 '<h4 class="psei-orders-text psei-orders-header">'. esc_html__('Test title 3', 'integrate-salesforce-and-elementor') .'</h4>';
       echo                 '<h6 class="psei-orders-text psei-orders-value">'. esc_html__('Sample Test 3', 'integrate-salesforce-and-elementor') .'</h6>';
       echo             '</div>';
       echo             '<div class="psei-orders-text-wrapper">';
       echo                 '<h4 class="psei-orders-text psei-orders-header">'. esc_html__('Test title 4', 'integrate-salesforce-and-elementor') .'</h4>';
       echo                 '<h6 class="psei-orders-text psei-orders-value">'. esc_html__('Sample Test 4', 'psintegrate-salesforce-and-elementorei') .'</h6>';
       echo             '</div>';
       echo         '</div>';
       echo     '</div>';
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

        protected function PSEIGetTableFields($table)
    {
        $this->PSEIInitializeSalesforce();
        $results = $this->salesforce->fetchObjectFields($table);
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

    protected function renderContent($fields, $button_label, $error_text, $login_error_text, $table) {
        $results = [];
        if (isset($_SESSION['psei_salesforce_user_id'])) {
            $this->PSEIInitializeSalesforce(); 
            $results = $this->salesforce->PSEIFetchUserOrderRecords($fields, sanitize_text_field($_SESSION['psei_salesforce_user_id']), 5, $table);
            
            error_log("$table Records" . "\n", 3, ABSPATH . 'error_log');
            error_log("===== Fields ==> " . json_encode($fields). "\n", 3, ABSPATH . 'error_log');
            error_log(json_encode($results) . "\n", 3, ABSPATH . 'error_log');
            error_log("==========================" . "\n", 3, ABSPATH . 'error_log');
        } else {
            error_log("Order Widget: Not Logged In ============" . "\n", 3, ABSPATH . 'error_log');
        }
        if ($results) {
            if ($results['error']) {
                $this->error = $results['error_description'];
            } else {
                $this->records = $results['data'];


                if($fields == "" && (sizeof($results['data']) > 0)){
                    // $fields = array_keys($results['data'][0]);
                    $fields = array_keys( (array) $results['data'][0]);
                    unset($fields[0]);
                    // $fields = $fields;
                }
            }
            if ($this->error) {
                ?>
                <div class="error-message">
                    <?php printf(
                            esc_html__( '%s.', 'integrate-salesforce-and-elementor' ),
                            esc_html($error_text)
                          ); ?>
                    <?php echo wp_json_encode($results); ?>
                </div>

                <?php
            } else {
?>
                <div class="psei-order-card">
                    <div class="psei-order-card-content">
                        <div class="psei-order">
                            <?php
                            // foreach ($this->records as $record) {
                            //     foreach ($fields as $field) {
                            ?>                         

                                <!-- <div class="psei-orders-text-wrapper">
                                    <h4 class="psei-orders-text psei-orders-header"><?php // echo $field ?> </h4>
                                    <h6 class="psei-orders-text psei-orders-value"><?php //echo $record->$field ?></h6>
                                </div> -->
                            <?php
                                // }
                            // }
                            ?>

                        <h3><b><?php echo esc_html($table);?></b> <?php esc_html__('Table', 'integrate-salesforce-and-elementor') ?></h3><br/>
                        <table class="psei-orders-table">
                            <thead><tr>
                            <?php
                                foreach ($fields as $field) {
                            ?>                         
                                    <th><?php echo esc_html($field) ?></th>
                            <?php
                                }
                            ?>
                            </tr></thead>
                            <tbody>
                            <?php
                                foreach ($this->records as $record) {  
                            ?>   
                            <tr>
                                <?php
                                    foreach ($fields as $field) {
                                ?>  
                                    <td><?php echo (is_object($record->$field) ? wp_json_encode($record->$field) : esc_html($record->$field)); ?></td>
                                    <?php } ?>
                            </tr>
                            <?php } ?>
                                
                            </tbody>
                            
                        </table><br/>
                        <!-- <button type="button" class="psei-btn psei-update-profile-button psei-btn-primary">
                            <?php // echo $button_label ?>
                        </button> -->
                        </div>
                    </div>
                </div>
<?php
           }
        } else {
            ?>
                <div class="error-message">
                    <?php printf(
                            esc_html__( '%s.', 'integrate-salesforce-and-elementor' ),
                            esc_html($login_error_text)
                          ); ?>
                </div>
            <?php
        }
    }

    protected function PSEIGetTableOptions(){
        $this->PSEIInitializeSalesforce();
        $results = $this->salesforce->PSEIGetObjects();
        $fieldOptions = [];
        if ($results['error']) {
            $this->error = $results['error_description'].'. Tables could not be fetched.';
        } else {
            $res = $results['data'];
            $response = $res->sobjects;
            $sObjects = array_column($response, 'name');
            foreach($sObjects as $object){
                $fieldOptions[$object] = $object;
            }
        }
        return $fieldOptions;
    }

}
