<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once( PSEI_INCLUDES_PATH . 'salesforce.php' );

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

use Productive_Salesforce_Elementor_Integration\Salesforce;

class PSEI_Table_View_Widget extends Widget_Base {
    private $error = '';
    private $tables = [];
    private $salesforce;
    private $table = '';


    public function get_name() {
        return 'table-view';
    }

    public function get_title() {
        return esc_html__( 'Table View', 'elemetix' );
    }

    public function get_icon() {
        return 'eicon-database';
    }

    public function get_categories() {
        return [ 'esei-salesforce' ];
    }

    public function get_keywords() {
        return [ 'salesforce', 'sfcc', 'table view' ];
    }

    public function get_script_depends() {
        return [ 'psei-table-view-widget' ];
    }

    protected function register_controls() {
        $this->PSEIFetchSFData();

        $tableOptions = [];
        foreach ( $this->tables as $table ) {
            $tableOptions[ $table ] = $table;
        }

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'elemetix' ),
            ]
        );

        $this->add_control(
            'table',
            [
                'label' => esc_html__( 'Table', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => $tableOptions,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'column_title',
            [
                'label' => esc_html__( 'Column Title', 'elemetix' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Title', 'elemetix' ),
            ]
        );

        $repeater->add_control(
            'field',
            [
                'label' => esc_html__( 'Field', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => [],
            ]
        );
        

        $repeater->add_control(
            'data_type',
            [
                'label' => esc_html__( 'Data Type', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'text'  => esc_html__( 'Text', 'elemetix' ),
                    'date'  => esc_html__( 'Date', 'elemetix' ),
                    'number'  => esc_html__( 'Number', 'elemetix' ),
                ],
                'default' => 'text'
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => esc_html__( 'Columns', 'elemetix' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ column_title }}}',
                'label_block' =>  true
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' =>  esc_html__( 'Number of Items to fetch e.g. 5', 'elemetix' ),
                'type' =>  Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1
            ]
        );

        $this->add_control(
            'can_move_to_another_page',
            [
                'label' => esc_html__( 'Can move to another page?', 'elemetix' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'elemetix' ),
                'label_off' => esc_html__( 'No', 'elemetix' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'next_page',
            [
                'label' => esc_html__( 'Next Page', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => $this->PSEIGetPages(),
                'condition' => [
                    'can_move_to_another_page' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'foreign_key',
            [
                'label' => esc_html__( 'Foreign Key', 'elemetix' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Foreign Key', 'elemetix' ),
                'condition' => [
                    'can_move_to_another_page' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $table = $settings[ 'table' ];
        $columns = $settings[ 'columns' ];
        $limit = $settings[ 'limit' ];
    
        // Check if we're in the Elementor editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
    
        if ( ! $is_editor && ! isset( $_SESSION[ 'psei_salesforce_user_id' ] ) ) {
            echo '<div class="error-message">' . esc_html__( 'Please login to view your data.', 'elemetix' ) . '</div>';
            return;
        }
    
        if ( isset( $table ) && isset( $columns ) && ! empty( $columns ) && ! empty( $table ) ) {
            $fields = [];
            $columnNames = [];
            foreach( $columns as $column ) {
                array_push( $fields, $column[ 'field' ] );
                array_push( $columnNames, $column[ 'column_title' ] );
            }
            $this->PSEIInitializeSalesforce();
    
            if ( $is_editor ) {
                // In editor, fetch records without user ID filter
                $results = $this->salesforce->PSEIFetchObjectRecords( $table, $fields, $limit );
            } else {
                // For logged-in users, fetch only their records
                $user_id = sanitize_text_field( $_SESSION[ 'psei_salesforce_user_id' ] );
                $results = $this->salesforce->PSEIFetchUserOrderRecords( $fields, $user_id, $limit, $table );
            }
    
            if ( isset( $results[ 'error' ] ) ) {
                echo '<div class="error-message">' . esc_html( $results[ 'error_description' ] ) . '</div>';
            } else {
                $records = $results[ 'data' ];
                $this->renderTable( $records, $columnNames, $fields, $columns );
            }
        } else {
            echo esc_html__( 'Please configure the Salesforce table and columns in the widget settings.', 'elemetix' );
        }
    }

    protected function content_template() {
        ?>
        <# if ( elementorFrontend.isEditMode() ) { #>
            <# if ( settings.table && settings.columns && settings.columns.length > 0 ) { #>
                <table class="psei-report-table">
                    <thead>
                        <tr class="psei-report-table-row">
                        <# _.each(settings.columns, function(column) { #>
                            <th>{{{ column.column_title }}}</th>
                        <# }); #>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="psei-report-table-body-row">
                            <td colspan="{{ settings.columns.length }}">
                                <?php echo esc_html__( 'Loading data...', 'elemetix' ); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <# } else { #>
                <p><?php echo esc_html__( 'Please configure the Salesforce table and columns in the widget settings.', 'elemetix' ); ?></p>
            <# } #>
        <# } else { #>
            <p><?php echo esc_html__( 'Please login to view your data.', 'elemetix' ); ?></p>
        <# } #>
        <?php
    }

    protected function PSEIInitializeSalesforce() {
        $saved_salesforce_token = get_option( 'psei_salesforce_token' );
        $saved_salesforce_username = get_option( 'psei_salesforce_username' );
        $saved_salesforce_password = get_option( 'psei_salesforce_password' );
        $saved_salesforce_client_id = get_option( 'psei_salesforce_client_id' );
        $saved_salesforce_client_secret = get_option( 'psei_salesforce_client_secret' );
        $saved_salesforce_instance_url = get_option( 'psei_salesforce_instance_url' );
        $saved_salesforce_access_token = get_option( 'psei_salesforce_access_token' );
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

    protected function PSEIFetchSFData() {
        $this->PSEIInitializeSalesforce();
        $results = $this->salesforce->PSEIGetObjects();
        if ( $results[ 'error' ] ) {
            $this->error = $results[ 'error_description' ].'. Tables could not be fetched.';
        } else {
            $res = $results[ 'data' ];
            $response = $res->sobjects;
            $this->tables = array_column( $response, 'name' );
        }
    }

    protected function PSEIGetTableFields( $table ){
        $this->PSEIInitializeSalesforce();
        $results = $this->salesforce->PSEIFetchObjectFields( $table );
        $fieldOptions = [];            
        if ( $results[ 'error' ] ) {
            $this->error = $results[ 'error' ];
        } else {
            foreach ( $results[ 'data' ] as $field ) {
                $fieldOptions[ $field->name ] = $field->label;
            }
        }
        return $fieldOptions;
    }

    public function get_table_fields( $table ) {
        return $this->PSEIGetTableFields( $table );
    }

    protected function renderTable( $records, $columnNames, $fields, $columns ) {
        ?>
        <table class="psei-report-table">
            <thead>
                <tr class="psei-report-table-row">
                <?php foreach ( $columnNames as $columnName ): ?>
                    <th><?php echo esc_html( $columnName ) ?></th>
                <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $records ) ): ?>
                    <tr>
                        <td colspan="<?php echo count( $columnNames ); ?>">
                            <?php echo esc_html__( 'No data found.', 'elemetix' ); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ( $records as $record ): ?>
                        <tr class="psei-report-table-body-row">
                        <?php foreach ( $fields as $index => $field ): ?>
                            <td><?php 
                                $data = isset( $record->$field ) ? $record->$field : '';
                                if( $columns[ $index ][ 'data_type' ] == 'date' && ! empty( $data ) ) {
                                    $strtotime = strtotime( $data );
                                    $data = date( 'd/m/Y', $strtotime );
                                }
                                echo esc_html( $data );
                            ?></td>
                        <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    protected function PSEIGetPages()
    {
        $pages = get_pages();
        $options = [];
        foreach ( $pages as $page ) {
            $options[ $page->ID ] = $page->post_title;
        }
        return $options;
    }
}