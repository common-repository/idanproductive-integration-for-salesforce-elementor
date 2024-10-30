<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
require_once(PSEI_INCLUDES_PATH . 'salesforce.php');

use Productive_Salesforce_Elementor_Integration\Salesforce;


class PSEI_Orders_Card_Widget extends \Elementor\Widget_Base
{
  private $salesforce;
  private $error;
  private $records;


  public function __construct($data = [], $args = null)
  {
    parent::__construct($data, $args);

    add_action('wp_enqueue_scripts', function () {
      wp_register_script('script-handle', PSEI_ASSETS_URL . 'js/psei-script.js', array('jquery'), '1.0.1', true);

      $js_info = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('psei_login_nonce_action')
      );

      wp_localize_script('script-handle', 'js_psei_login', $js_info);
      wp_enqueue_script('script-handle');
    });

    do_action('wp_enqueue_scripts');
    
    add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_dynamic_scripts' ] );

  }

  public function enqueue_dynamic_scripts() {
    wp_enqueue_script(
        'psei-orders-card-widget',
        PSEI_ASSETS_URL . 'js/psei-orders-card-editor.js',
        ['jquery', 'elementor-editor'],
        '1.0.0',
        true
    );
    wp_localize_script('psei-orders-card-widget', 'psei_ajax_object', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('psei_ajax_nonce')
    ]);
}
  

  public function get_name()
  {
    return 'orders-card';
  }

  public function get_title()
  {
    return __('Orders - Card View', 'elemetix');
  }

  public function get_icon()
  {
    return 'eicon-document-file';
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
        'label' => __('Content', 'elemetix'),
      ]
    );

    $this->add_control(
      'table',
      [
        'label' => __('Table', 'elemetix'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'options' => $tableOptions,
        'default' => 'order_table__c',
      ]
    );

    
    $this->add_control(
        'fields',
        [
          'label' => __('Fields', 'elemetix'),
          'type' => \Elementor\Controls_Manager::SELECT2,
          'multiple' => true,
          'options' => [],
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

    $this->add_control(
      'error_text',
      [
        'label' => __('No Records Text', 'elemetix'),
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => __('No records found', 'elemetix'),
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

    $this->end_controls_section();

    $this->start_controls_section(
      'card_section_style',
      [
        'label' => esc_html__('Card Style', 'elemetix'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'card_section_margin',
      [
        'label' => esc_html__('Card Margin', 'elemetix'),
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
        'label' => esc_html__('Section Padding', 'elemetix'),
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
        'label' => esc_html__('Button Border Radius', 'elemetix'),
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
        'label' => esc_html__('Background', 'elemetix'),
        'types' => ['classic', 'gradient',],
        'selector' => '{{WRAPPER}} .psei-order-card',
      ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
      'button_section_style',
      [
        'label' => esc_html__('Button Style', 'elemetix'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_group_control(
      \Elementor\Group_Control_Background::get_type(),
      [
        'name' => 'button_background',
        'label' => esc_html__('Background', 'elemetix'),
        'types' => ['classic', 'gradient',],
        'selector' => '{{WRAPPER}} button',
      ]
    );

    $this->add_control(
      'button_color',
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
      'button_border_color',
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
      'button_margin',
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
      'button_border_radius',
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
      'button_width',
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

    // Text styling controls
    $this->start_controls_section(
      'section_text_style',
      [
        'label' => esc_html__('Text Style', 'elemetix'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'text_wrapper_padding',
      [
        'label' => esc_html__('Text wrapper padding', 'elemetix'),
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
        'label' => esc_html__('Header Text Color', 'elemetix'),
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
        'label' => esc_html__('Text Color', 'elemetix'),
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
        'label' => esc_html__('Text Wrapper Width', 'elemetix'),
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
        'label' => esc_html__('Text Wrapper Margin', 'elemetix'),
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
        'label' => esc_html__('Text Margin', 'elemetix'),
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
        'label' => esc_html__('Header Font Size', 'elemetix'),
        'type' => \Elementor\Controls_Manager::SLIDER,
        'size_units' => ['px', 'em', 'rem'],
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
        'label' => esc_html__('Text Font Size', 'elemetix'),
        'type' => \Elementor\Controls_Manager::SLIDER,
        'size_units' => ['px', 'em', 'rem'],
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

  // Fetch fields for a given table
  public function get_table_fields( $table ) {
    $fields = $this->PSEIGetTableFields( $table );
    return $fields;
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

  protected function renderContent($fields, $button_label, $error_text, $login_error_text, $table)
{ 
    // Check if we're in the Elementor editor
    $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

    if (!$is_editor && !isset($_SESSION['psei_salesforce_user_id'])) {
        echo '<div class="error-message">' . esc_html($login_error_text) . '</div>';
        return;
    }

    $this->PSEIInitializeSalesforce();
    $results = $this->salesforce->PSEIGetAccessToken();
    if ($results && isset($results->access_token)) {
        $access_token = $results->access_token;
    } else {
        echo '<div class="error-message">' . esc_html__('Failed to obtain access token', 'elemetix') . '</div>';
        return;
    }

    if (empty($fields)) {
        $fieldOptions = $this->PSEIGetTableFields($table);
        $fields = array_keys($fieldOptions);
    }

    $field_labels = $this->PSEIGetTableFields($table);

    $relationships = $this->salesforce->PSEIFetchSObjectRelationships($table);
    $relationship_columns = array_column($relationships, 'name');

    if ($is_editor) {
        // In editor, fetch 5 records
        $results = $this->salesforce->PSEIFetchObjectRecords($table, $fields, 5);
    } else {
        // For logged-in users, fetch only their records
        $user_id = sanitize_text_field($_SESSION['psei_salesforce_user_id']);
        $results = $this->salesforce->PSEIFetchUserOrderRecords($fields, $user_id, 5, $table);
    }

    if ($results && !isset($results['error'])) {
        $records = $results['data'];
        ?>
        <input type="hidden" id="psei_access_token" value="<?php echo esc_attr($access_token); ?>">
        <input type="hidden" id="psei_sobject" value="<?php echo esc_attr($table); ?>">
        <?php
        foreach ($records as $record) {
            ?>
            <div class="psei-order-card" style="padding-top: 20px">
                <div class="psei-profile-header" style="margin: 30px 0px 0px 30px;">
                    <div style="display: inline-flex; flex-direction: row; align-items: center;">
                        <div>
                            <svg style="width: 50px" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="42" height="42" rx="8" fill="#EAF1FF" />
                                <path d="M21 20.6618C19.583 20.6618 18.4236 20.2109 17.5218 19.3092C16.62 18.4074 16.1691 17.248 16.1691 15.8309C16.1691 14.4138 16.62 13.2544 17.5218 12.3527C18.4236 11.4509 19.583 11 21 11C22.4171 11 23.5765 11.4509 24.4783 12.3527C25.3801 13.2544 25.831 14.4138 25.831 15.8309C25.831 17.248 25.3801 18.4074 24.4783 19.3092C23.5765 20.2109 22.4171 20.6618 21 20.6618ZM12.6265 31C12.0897 31 11.6334 30.8121 11.2577 30.4364C10.882 30.0607 10.6941 29.6044 10.6941 29.0676V27.9726C10.6941 27.1567 10.8981 26.4589 11.306 25.8792C11.714 25.2995 12.24 24.8594 12.8841 24.5588C14.3226 23.9147 15.7021 23.4316 17.0226 23.1095C18.343 22.7874 19.6689 22.6264 21 22.6264C22.3312 22.6264 23.6517 22.7928 24.9614 23.1256C26.2711 23.4584 27.6452 23.9361 29.0838 24.5588C29.7494 24.8594 30.2861 25.2995 30.6941 25.8792C31.102 26.4589 31.306 27.1567 31.306 27.9726V29.0676C31.306 29.6044 31.1181 30.0607 30.7424 30.4364C30.3667 30.8121 29.9104 31 29.3736 31H12.6265Z" fill="#2D7AFF" />
                            </svg>
                        </div>
                        <div class="psei-profile-name-details">
                            <span class="psei-profile-user-name psei-profile-text-value"><?php echo esc_html($table); ?></span><br />
                            <span class="psei-profile-card-details">Active Record</span>
                        </div>
                    </div>
                </div>
                <div class="psei-order-card-content">
                    <div class="psei-profile-input-group" style="flex-wrap: wrap;">
                        <div style="display: inline-flex; flex-direction: row; align-items: center; flex-wrap: wrap;">
                            <?php
                            foreach ($fields as $field) {
                                $is_relationship = in_array($field, $relationship_columns);
                                $field_label = isset($field_labels[$field]) ? $field_labels[$field] : $field;
                                ?>
                                <div class="psei-profile-name-details <?php echo $is_relationship ? 'popover__wrapper' : ''; ?>" style="margin: 30px;">
                                    <?php if ($is_relationship): ?>
                                        <span class="psei-profile-text-value">
                                            <a class='popover-link' data-record_id="<?php echo esc_attr($record->Id); ?>" data-relationship="<?php echo esc_attr($relationships[array_search($field, $relationship_columns)]['relationshipName']); ?>">
                                                <?php echo esc_html($record->$field ?? '-'); ?>
                                            </a>
                                        </span>
                                    <?php else: ?>
                                        <span class="psei-profile-text-value"><?php echo esc_html($record->$field ?? '-'); ?></span>
                                    <?php endif; ?>
                                    <br />
                                    <label class="psei-profile-input-label psei-profile-text-label" for="<?php echo esc_attr($field); ?>"><?php echo esc_html($field_label); ?></label>
                                    <?php if ($is_relationship): ?>
                                        <div class="popover__content">
                                            <div class="psei-profile-input-group" style="flex-wrap: wrap;">
                                                <div class="grid-container">
                                                    <b id="popover_relationship_name">-</b>
                                                    <img id="loading" class="psei-spinner" style="display: block" />
                                                    <div class="psei-profile-name-details grid-item" style="margin: 30px; display: none;">
                                                        <span class="psei-profile-text-value">Value</span><br />
                                                        <label class="psei-profile-input-label psei-profile-text-label">Title</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
    } else {
        echo '<div class="error-message">' . esc_html($error_text) . '</div>';
    }
}

  protected function PSEIGetTableOptions()
  {
    $this->PSEIInitializeSalesforce();
    $results = $this->salesforce->PSEIGetObjects();
    $fieldOptions = [];
    if ($results['error']) {
      $this->error = $results['error_description'] . '. Tables could not be fetched.';
    } else {
      $res = $results['data'];
      $response = $res->sobjects;
      $sObjects = array_column($response, 'name');
      foreach ($sObjects as $object) {
        $fieldOptions[$object] = $object;
      }
    }
    return $fieldOptions;
  }




}
