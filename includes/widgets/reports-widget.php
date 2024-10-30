<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;

use Productive_Salesforce_Elementor_Integration\Salesforce;

class PSEI_Reports_Widget extends Widget_Base {
    private $salesforce;
    private $error;
    private $records;

    public function get_name() {
        return 'reports';
    }

    public function get_title() {
        return esc_html__( 'Reports', 'elemetix' );
    }

    public function get_icon() {
        return 'eicon-document-file';
    }

    public function get_categories() {
        return [ 'esei-salesforce' ];
    }

    public function get_keywords() {
        return [ 'salesforce', 'sfcc', 'reports', 'salesforce reports' ];
    }

    public function get_scripts_depends() {
        return [ 'chartjs' ];
    }    

    public function register_controls() {

        $reportOptions = $this->PSEIGetReportOptions();

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'elemetix' ),
            ]
        );
        
        $this->add_control(
            'object',
            [
                'label' => esc_html__( 'Select Report', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => $reportOptions,
            ]
        );

        $this->add_control(
            'report_type',
            [
                'label' => esc_html__( 'Report Type', 'elemetix' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'table' => esc_html__( 'Table', 'elemetix' ),
                    'bar'   => esc_html__( 'Bar', 'elemetix' ),
                    'line' => esc_html__( 'Line', 'elemetix' ),
                    'donut' => esc_html__( 'Donut / Pie', 'elemetix' ),
                    'column' => esc_html__( 'Column', 'elemetix' ),
                    'stacked_bar' => esc_html__( 'Stacked Bar', 'elemetix' ),
                    'stacked_column' => esc_html__( 'Stacked Column', 'elemetix' ),
                    'scatter_plot' => esc_html__( 'Scatter Plot', 'elemetix' )
                ],
                'default' => 'table'
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'elemetix' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Title', 'elemetix' ),
            ]
        );

        $this->add_control(
            'description',
            [
                'label' => esc_html__( 'Description', 'elemetix' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Lorem ipsum dolor sit amet' ),
            ]
        );

        $this->add_control(
            'login_error_text',
            [
              'label' => esc_html__( 'Not logged In Error Text', 'elemetix' ),
              'type' => Controls_Manager::TEXT,
              'default' => esc_html__( 'Please login to view your reports', 'elemetix' ),
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'section_style_general',
            [
                'label' => esc_html__('General Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'title_position',
            [
                'label' => esc_html__('Title Position', 'elemetix'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'elemetix'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'elemetix'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'elemetix'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .psei-dashboard h2' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Title Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-dashboard h2' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .psei-dashboard h2',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_text_shadow',
                'selector' => '{{WRAPPER}} .psei-dashboard h2',
            ]
        );

        $this->add_responsive_control(
            'description_position',
            [
                'label' => esc_html__('Description Position', 'elemetix'),
                'type' => Controls_Manager::CHOOSE,
                'separator' => 'before',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'elemetix'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'elemetix'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'elemetix'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .psei-dashboard h3' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => esc_html__('Description Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-dashboard h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .psei-dashboard h3',
            ]
        );

        $this->end_controls_section();

        // Style Section - Table
        $this->start_controls_section(
            'section_style_table',
            [
                'label' => esc_html__('Table Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'report_type' => 'table',
                ],
            ]
        );

        // Table Header Style
        $this->add_control(
            'table_header_background_color',
            [
                'label' => esc_html__('Header Background Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table thead' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_text_color',
            [
                'label' => esc_html__('Header Text Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'table_header_typography',
                'selector' => '{{WRAPPER}} .psei-report-table th',
            ]
        );

        // Table Body Style
        $this->add_control(
            'table_body_background_color',
            [
                'label' => esc_html__('Body Background Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table tbody' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_body_text_color',
            [
                'label' => esc_html__('Body Text Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'table_body_typography',
                'selector' => '{{WRAPPER}} .psei-report-table td',
            ]
        );

        // Table Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => esc_html__('Border', 'elemetix'),
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .psei-report-table, {{WRAPPER}} .psei-report-table th, {{WRAPPER}} .psei-report-table td',
            ]
        );

        // Table Cell Padding
        $this->add_responsive_control(
            'table_cell_padding',
            [
                'label' => esc_html__('Cell Padding', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table th, {{WRAPPER}} .psei-report-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Table Hover Styles
        $this->add_control(
            'table_row_hover_background_color',
            [
                'label' => esc_html__('Row Hover Background Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-report-table tbody tr:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Chart
        $this->start_controls_section(
            'section_style_chart',
            [
                'label' => esc_html__('Chart Style', 'elemetix'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'report_type!' => 'table',
                ],
            ]
        );

        $this->add_control(
            'chart_color',
            [
                'label' => esc_html__('Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4e73df',
            ]
        );

        $this->add_control(
            'legend_color',
            [
                'label' => esc_html__('Legend Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container .chartjs-legend' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'legend_typography',
                'label' => esc_html__('Legend Typography', 'elemetix'),
                'selector' => '{{WRAPPER}} .psei-chart-container .chartjs-legend',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => esc_html__('Label Color', 'elemetix'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container .chartjs-axis-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => esc_html__('Label Typography', 'elemetix'),
                'selector' => '{{WRAPPER}} .psei-chart-container .chartjs-axis-label',
            ]
        );   
        
        $this->add_control(
            'chart_legend_position',
            [
                'label' => esc_html__('Legend Position', 'elemetix'),
                'type' => Controls_Manager::SELECT,
                'default' => 'top',
                'options' => [
                    'top' => esc_html__('Top', 'elemetix'),
                    'bottom' => esc_html__('Bottom', 'elemetix'),
                    'left' => esc_html__('Left', 'elemetix'),
                    'right' => esc_html__('Right', 'elemetix'),
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'chart_background',
                'label' => esc_html__('Background', 'elemetix'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .psei-chart-container',
            ]
        );

        

        $this->add_responsive_control(
            'chart_width',
            [
                'label' => esc_html__('Width', 'elemetix'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'separator' => 'before',
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'chart_height',
            [
                'label' => esc_html__('Height', 'elemetix'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 400,
                ],
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'chart_border_radius',
            [
                'label' => esc_html__('Border Radius', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'chart_background_background' => ['classic', 'gradient'],
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'chart_border',
                'selector' => '{{WRAPPER}} .psei-chart-container',
                'condition' => [
                    'chart_background_background' => ['classic', 'gradient'],
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'chart_box_shadow',
                'selector' => '{{WRAPPER}} .psei-chart-container',
            ]
        );

        $this->add_responsive_control(
            'chart_padding',
            [
                'label' => esc_html__('Padding', 'elemetix'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .psei-chart-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );      

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $login_error_text = $settings[ 'login_error_text' ];

        // Check if we're in the Elementor editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if ( ! $is_editor && ! isset( $_SESSION[ 'psei_salesforce_user_id' ] ) ) {
            // User is not logged in and we're not in the editor
            echo '<div class="error-message">' . esc_html( $login_error_text ) . '</div>';
            return;
        }

        $this->renderContent( $settings );
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
    
    protected function PSEIGetReportOptions() {
        if ( ! isset( $this->salesforce ) ) {
            $this->PSEIInitializeSalesforce();
        }

        // Fetch all the report
        $results = $this->salesforce->PSEIGetReports();
        
        $fieldOptions = [];
        if ( $results[ 'error' ]) {
            $this->error = $results[ 'error_description' ] . '. Reports could not be fetched.';
        } else {
            $reports = $results[ 'data' ];
            foreach ( $reports as $report ) {
                $id = $report->Id;
                $fieldOptions[ $id ] = $report->Name . ' (' . $report->Format . ')';
            }
        }
        return $fieldOptions;
    }

    private function renderContent( $settings ) {
        if ( ! isset( $this->salesforce ) ) {
            $this->PSEIInitializeSalesforce();
        }

        $results = $this->salesforce->PSEIFetchReportRecords( $settings[ 'object' ] );

        if ( $results[ 'error' ] ) {
            echo '<div class="error-message">' . esc_html( $results[ 'error_description' ] ) . '</div>';
            return;
        }

        $this->records = $results[ 'data' ];

        ?>
        <div class="psei-dashboard">
            <h2><?php echo esc_html( $settings[ 'title' ] ); ?></h2>
            <h3><?php echo esc_html( $settings[ 'description' ] ); ?></h3>
            <?php 

            if ( $this->records ): 
                $reportFormat = $this->records->reportMetadata->reportFormat;
                $reportData = $this->records->factMap->{ '0!T' }->rows;
                $reportColumns = $this->records->reportMetadata->detailColumns;
                $groupingsDown = $this->records->groupingsDown->groupings;
                $columnInfo = $this->records->reportExtendedMetadata->detailColumnInfo;

                if ( $settings[ 'report_type' ] === 'table' || $reportFormat !== 'SUMMARY' ) {
                    $this->renderTable( $reportData, $reportColumns, $columnInfo, $groupingsDown );
                } else {
                    $this->renderChart( $this->records );
                }
            else: 
                echo '<div class="psei-error-warning"><p>' . esc_html__( 'There are no records', 'elemetix' ) . '</p></div>';
            endif; 
            ?>
        </div>
        <?php
    }

    private function renderChart($reportData) {
        $settings = $this->get_settings_for_display();
        $labels = [];
        $datasets = [];
        $groupingsDown = $reportData->groupingsDown;
        $chartType = $reportData->reportMetadata->chart->chartType;
        
        if (isset($groupingsDown->groupings) && is_array($groupingsDown->groupings)) {
            foreach ($groupingsDown->groupings as $grouping) {
                $labels[] = $grouping->label;
            }
        } else {
            $labels = ['Total'];
        }
    
        // Extract data and label from factMap and reportExtendedMetadata
        $data = [];
        $datasetLabel = '';
        if (isset($reportData->factMap->{'T!T'}->aggregates[0])) {
            $data[] = $reportData->factMap->{'T!T'}->aggregates[0]->value;
            $aggregateKey = $reportData->reportMetadata->aggregates[0];
            $datasetLabel = $reportData->reportExtendedMetadata->aggregateColumnInfo->$aggregateKey->label;
        } elseif (isset($reportData->factMap->{'0!T'}->aggregates[0])) {
            $data[] = $reportData->factMap->{'0!T'}->aggregates[0]->value;
            $aggregateKey = $reportData->reportMetadata->aggregates[0];
            $datasetLabel = $reportData->reportExtendedMetadata->aggregateColumnInfo->$aggregateKey->label;
        } else {
            $data[] = 0;
            $datasetLabel = 'No Data';
            error_log('No data found in factMap');
        }
    
        // Prepare dataset
        $datasets[] = [
            'label' => $datasetLabel,
            'data' => $data,
            'backgroundColor' => $settings['chart_color'],
            'borderColor' => $settings['chart_color'],
        ];
    
        $chartjsType = $this->mapChartType($chartType);
        $isHorizontal = in_array($chartType, ['Bar', 'Stacked Bar', 'Horizontal Bar']);

    
        $chartData = [
            'type' => $chartjsType,
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => $settings['chart_legend_position'],
                        'labels' => [
                            'color' => $settings['label_color'],
                            'font' => $labelFont,
                        ],
                    ],
                    'title' => [
                        'display' => false,
                        'text' => $reportData->reportMetadata->name,
                    ],
                ],
            ],
        ];
    
        // Only add scales for applicable chart types
        if (!in_array($chartjsType, ['pie', 'doughnut'])) {
            $chartData['options']['scales'] = [
                'x' => [
                    'ticks' => [
                        'color' => $settings['label_color'],
                        'font' => $labelFont
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'color' => $settings['label_color'],
                        'font' => $labelFont,
                    ],
                ],
            ];
        }
    
        // Center the point for line charts with a single data point
        if ($chartjsType === 'line' && count($data) === 1) {
            $chartData['data']['labels'] = ['', $labels[0], ''];
            $chartData['data']['datasets'][0]['data'] = [null, $data[0], null];
            $chartData['options']['scales']['x']['ticks']['display'] = false;
            $chartData['options']['scales']['y']['beginAtZero'] = true;
        }
    
        // Set indexAxis to 'y' for horizontal bar charts
        if ($isHorizontal) {
            $chartData['options']['indexAxis'] = 'y';
        }
    
        // Handle stacked charts
        if (strpos($chartType, 'Stacked') !== false) {
            $chartData['options']['scales']['x']['stacked'] = true;
            $chartData['options']['scales']['y']['stacked'] = true;
        }
    
        // Encode chart data for JavaScript
        $chartDataJson = wp_json_encode($chartData);
    
        // Render chart container and script
        ?>
        <div class="psei-chart-container">
            <canvas id="psei-chart-<?php echo esc_attr($this->get_id()); ?>"></canvas>
        </div>
        <script>
            function initializeChart_<?php echo esc_attr($this->get_id()); ?>() {
                var ctx = document.getElementById('psei-chart-<?php echo esc_attr($this->get_id()); ?>').getContext('2d');
                new Chart(ctx, <?php echo $chartDataJson; ?>);
            }
    
            if (window.elementorFrontend && window.elementorFrontend.isEditMode()) {
                elementorFrontend.hooks.addAction('frontend/element_ready/reports.default', function($scope) {
                    if ($scope.find('#psei-chart-<?php echo esc_attr($this->get_id()); ?>').length) {
                        initializeChart_<?php echo esc_attr($this->get_id()); ?>();
                    }
                });
            } else {
                document.addEventListener('DOMContentLoaded', initializeChart_<?php echo esc_attr($this->get_id()); ?>);
            }
        </script>
        <?php
    }
    
    private function mapChartType( $salesforceChartType ) {
        $chartTypeMap = [
            'Bar' => 'bar',
            'Horizontal Bar' => 'bar',
            'Column' => 'bar',
            'Stacked Bar' => 'bar',
            'Stacked Column' => 'bar',
            'Line' => 'line',
            'Donut' => 'doughnut',
            'Funnel' => 'funnel',
            'Scatter Plot' => 'scatter'
        ];
    
        $mappedType = isset( $chartTypeMap[ $salesforceChartType ] ) ? $chartTypeMap[ $salesforceChartType ] : 'bar';
        return $mappedType;
    }
    
    private function getRandomColor() {
        return '#' . str_pad( dechex( wp_rand( 0, 0xFFFFFF ) ), 6, '0', STR_PAD_LEFT );
    }
    
    private function renderTable( $reportData, $reportColumns, $columnInfo, $groupingsDown ) {
        ?>
        <table class="psei-report-table">
            <thead>
                <tr class="psei-report-table-row">
                    <?php foreach ( $reportColumns as $column ): ?>
                        <th><?php echo esc_html( $columnInfo->$column->label ); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $reportData as $row ): ?>
                    <tr class="psei-report-table-body-row">
                        <?php foreach ( $row->dataCells as $cell ): ?>
                            <td><?php echo esc_html( $cell->label ); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }  

}