<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
require_once( PSEI_INCLUDES_PATH . 'salesforce.php' );

use Productive_Salesforce_Elementor_Integration\Salesforce;


class PSEI_Salesforce_Addon_Widget extends \Elementor\Widget_Base {

    private $error = '';
    private $tables = [];
    private $salesforce;
    private $table = '';

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
  
        wp_register_script( 'script-handle', PSEI_ASSETS_URL . 'js/psei-script.js', array( 'jquery' ), PSEI_PLUGIN_VERSION, true );

    }

    public function get_script_depends() {
        return [ 'script-handle' ];
    }

        public function get_name() {
            return 'salesforce-addon';
        }
    
        public function get_title() {
            return __( 'Salesforce Addon', 'integrate-salesforce-and-elementor' );
        }
    
        public function get_icon() {
            return 'eicon-database';
        }
    
        public function get_categories() {
            return [ 'esei-salesforce' ];
        }

        public function get_keywords() {
            return [ 'salesforce', 'sfcc' ];
        }
    
        protected function register_controls() {
            $this->PSEIFetchSFData();

            // create an array with key = table and value = table              
            $tableOptions = [];
            foreach ($this->tables as $table) {
                $tableOptions[$table] = $table;
            }
    
            $this->start_controls_section(
                'section_content',
                [
                    'label' => __( 'Content', 'integrate-salesforce-and-elementor' ),
                ]
            );
    
            $this->add_control(
                'table',
                [
                    'label' => __( 'Table', 'integrate-salesforce-and-elementor' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $tableOptions,
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

            
    
            $this->end_controls_section();
    
        }
    
        protected function render() {
            $settings = $this->get_settings_for_display();
            $table= $settings['table'];
            $fields = $settings['fields'];
            // echo json_encode($fields);

            if(isset($table) && isset($fields) && !empty($fields) && !empty($table)) {
                // $results = $this->salesforce->fetchObjectRecords($table, $fields);
                // $records = $results['data'];
                // $this->renderTable($records, $fields);
            }

            
                                
        }
    
        protected function content_template() {
        //     $settings = $this->get_settings_for_display();
        //     $table= $settings['table'];
        //     $fields = $settings['fields'];

        //     $results = $this->salesforce->fetchObjectRecords($table, $fields);
        //     $records = $results['data'];

        //    // echo json_encode($records);

        //     $this->renderTable($records, $fields);
            ?>
            <!-- <#
            view.addInlineEditingAttributes( 'text', 'basic' );
            view.addRenderAttribute( 'text', 'class', 'text' );
            #> -->
            <!-- <div {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.text }}}</div> -->
            
            <?php
        }

        protected function PSEIFetchSFData(){
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
            $results = $this->salesforce->PSEIGetObjects();
            if ($results['error']) {
                $this->error = $results['error_description'].'. Tables could not be fetched.';
            } else {
                $res = $results['data'];
                $response = $res->sobjects;
                $this->tables = array_column($response, 'name');
            }
        }

        protected function PSEIGetTableFields($table){
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

        protected function renderTable($records, $fields){
            ?>
            <table>
                <thead>
                    <tr>
                    <?php foreach ($fields as $field): ?>
                        <th><?php echo esc_html($field) ?></th>
                    <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record) { ?>
                        <tr>
                        <?php foreach ($fields as $field): ?>
                            <td><?php echo esc_html($record->$field) ?></td>
                        <?php endforeach; ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php
        }
}