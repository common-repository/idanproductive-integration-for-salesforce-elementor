<?php

// namespace Productive_Salesforce_Elementor_Integration;
use Productive_Salesforce_Elementor_Integration\Salesforce;


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class PSEI_Form_Action_Elementor extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

	private $salesforce;
	private $error;
	private $records;
	private $tableOptions;
	private $form_fields;


	public function __construct()
	{
		$this->tableOptions = $this->PSEIGetTableOptions();
		
	}

	public function get_name()
	{
		return 'salesforce';
	}

	public function get_label()
	{
		return esc_html__('Salesforce', 'elemetix');
	}

	public function register_settings_section($widget)
	{
		error_log("\n\n1. Form Action Widget ========" . "\n", 3, ABSPATH . 'error_log');
		
		$control = (array) $widget;

		/* 	error_log(" == Control ==" . "\n", 3, ABSPATH . 'error_log');
			error_log(json_encode($control) . "\n", 3, ABSPATH . 'error_log');
			error_log("==============" . "\n", 3, ABSPATH . 'error_log');
 */
			if(isset($control["\0Elementor\Controls_Stack\0data"])){
				update_option('elementor_form_fields', $control["\0Elementor\Controls_Stack\0data"]);
				$this->form_fields = get_option( 'elementor_form_fields', [] );
				// error_log(" == Data is Set ==" . "\n", 3, ABSPATH . 'error_log');
				// error_log(json_encode($this->form_fields) . "\n", 3, ABSPATH . 'error_log');
				// error_log("==============" . "\n", 3, ABSPATH . 'error_log');
			} else {
				$this->form_fields = get_option( 'elementor_form_fields', [] );
				// error_log(" == Data is NOT Set: Retrieving From ==" . "\n", 3, ABSPATH . 'error_log');
				// error_log(json_encode($this->form_fields) . "\n", 3, ABSPATH . 'error_log');
				// error_log("==============" . "\n", 3, ABSPATH . 'error_log');
			}

		
		// error_log('===================================================' . "\n", 3, ABSPATH . 'error_log');	
			if(!isset($this->form_fields)){
				// error_log('Form Data Not Set. Fetching.....' . "\n", 3, ABSPATH . 'error_log');	
				$this->form_fields = $widget->get_id();
				// error_log('Printing Data' . "\n", 3, ABSPATH . 'error_log');	
				// error_log(json_encode($widget->get_id()) . "\n", 3, ABSPATH . 'error_log');
			} else {
				// error_log('Form Data has been Set. Fetching.....' . "\n", 3, ABSPATH . 'error_log');	
			}


		// error_log("========================================", 3, ABSPATH . 'error_log');

		

		$widget->start_controls_section(
			'section_salesforce_object',
			[
				'label' => esc_html__('Save Values To Salesforce Object', 'elemetix'),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'table',
			[
					'label' => __('Table', 'elemetix'),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => $this->tableOptions,
					'default' => 'order_table__c',
			]
	);

		$control = (array) $this;
		$table = isset($control["\0Salesforce_Form_Action_Elementor\0form_fields"]['settings']['table'])
			? $control["\0Salesforce_Form_Action_Elementor\0form_fields"]['settings']['table']
			: '';

			error_log(" == Control ==" . "\n", 3, ABSPATH . 'error_log');
			error_log(json_encode($control) . "\n", 3, ABSPATH . 'error_log');
			error_log("==============" . "\n", 3, ABSPATH . 'error_log');


		$fieldOptions = [];
		if ($table) {
			$fieldOptions = $this->PSEIGetTableFields($table);

		// $widget->add_control(
		// 	'fields',
		// 	[
		// 		'label' => __('Select Table Fields', 'elemetix'),
		// 		'type' => \Elementor\Controls_Manager::SELECT2,
		// 		'multiple' => true,
		// 		'options' => $fieldOptions,
		// 	]
		// ); 

		error_log(" == Fields ==" . "\n", 3, ABSPATH . 'error_log');
			error_log(json_encode($this->form_fields["settings"]["form_fields"]) . "\n", 3, ABSPATH . 'error_log');
			error_log("==============" . "\n", 3, ABSPATH . 'error_log');

			// Iterate through the form fields, and create a custom field 
			// 	to map the form field to SF Field
			foreach ($this->form_fields["settings"]["form_fields"] as $index => $form_field) {
				$widget->add_control(
					'salesforce_form_config_' . $form_field["custom_id"],
					[
						'type' 					=> 'salesforce_form_mapping',
						'form_fields' 	=> $this->form_fields["settings"]["form_fields"],
						'table_fields' => $fieldOptions,
						'field_name' => $form_field["custom_id"],
						'field_label' => $form_field["field_label"],
						'field_id' => $form_field["_id"],
						'index' => $index,
						'form_settings' => $this->form_fields["settings"]
					]
				);
			}

	

	}




		/* 
	
	
		
		*/


		$widget->end_controls_section();
	}

	public function on_export($element)
	{
		error_log("\n = ON EXPORT ===========================" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($element) . "\n", 3, ABSPATH . 'error_log');
		error_log("\n===========================" .	"\n", 3, ABSPATH . 'error_log');
	}

	public function run($record, $ajax_handler)
	{
		$settings = $record->get( 'form_settings' );

		$table = $settings["table"];

		// Get submitted form data.
		$raw_fields = $record->get( 'fields' );

		error_log(" == Form Submissions ==" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($raw_fields) . "\n", 3, ABSPATH . 'error_log');
		error_log("==============" . "\n", 3, ABSPATH . 'error_log');

		error_log(" == Form Setings ==" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($settings) . "\n", 3, ABSPATH . 'error_log');
		error_log("==============" . "\n", 3, ABSPATH . 'error_log');


		// Normalize form data.
		$submissions = [];
		$mapping = [];
		$payload = [];


		foreach ( $raw_fields as $id => $field ) {
			$submissions[ $id ] = $field['value'];
			$mapping [$id] = $settings["salesforce_form_config_" . $id];
			$payload[$mapping [$id]] = $submissions[ $id ];
		}

		error_log(" == Mapping ==" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($submissions) . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($mapping) . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($payload) . "\n", 3, ABSPATH . 'error_log');
		error_log("==============" . "\n", 3, ABSPATH . 'error_log');

		$savedResponse = $this->salesforce->createNewSObjectRecord($table, $payload);
		error_log(" == Form Response ==" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($savedResponse) . "\n", 3, ABSPATH . 'error_log');
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

			if($response){
				$sObjects = array_column($response, 'name');
				foreach ($sObjects as $object) {
					$fieldOptions[$object] = $object;
				}
			}
			
		}
		return $fieldOptions;
	}
}
