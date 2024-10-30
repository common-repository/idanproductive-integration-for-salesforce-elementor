<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once(PSEI_INCLUDES_PATH . 'salesforce.php');

use Productive_Salesforce_Elementor_Integration\Salesforce;

/**
 * Custom message on success class.
 */
class PSEI_Form_Control
{

	private $salesforce;
	private $error;
	private $records;
	private $tableOptions;


	public function __construct()
	{
		add_action('elementor/element/form/section_integration/after_section_end', [$this, 'psei_add_message_control'], 100, 2);

		$this->tableOptions = $this->PSEIGetTableOptions();
	}

	/**
	 * add_css_class_field_control
	 * @param $element
	 * @param $args
	 */
	public function psei_add_message_control($element, $args)
	{
		$element->start_controls_section(
			'psei_form_control_section',
			[
				'label' => __('Connect To Salesforce', 'elemetix'),
			]
		);

		$element->add_control(
			'table',
			[
				'label' => __('Select SF Object', 'elemetix'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $this->tableOptions,
				'default' => 'order_table__c',
			]
		);

		$control = (array) $this;
		$table = isset($control["\0Elementor\Controls_Stack\0data"]['settings']['table'])
			? $control["\0Elementor\Controls_Stack\0data"]['settings']['table']
			: '';


		$fieldOptions = [];
		if ($table) {
			$fieldOptions = $this->PSEIGetTableFields($table);
		}

		$element->add_control(
			'fields',
			[
				'label' => __('Select Table Fields', 'elemetix'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $fieldOptions,
			]
		);


		// $element->add_control(
		// 	'salesforce_elementor_form_control_section_hide_form_after_submit',
		// 	[
		// 		'label' => __( 'Hide form after submit?', 'elemetix' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => __( 'Hide', 'elemetix' ),
		// 		'label_off' => __( 'Show', 'elemetix' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 		'description' => __( 'This option hide the form after success submit.', 'elemetix' ),
		// 	]
		// );

		// $element->add_control(
		// 	'salesforce_elementor_form_control_section_template-custom-success-message',
		// 	[
		// 		'label' => __( 'Message Template', 'elemetix' ),
		// 		'type' => Elementor\Controls_Manager::TEXT,
		// 		'placeholder' => __( '[your-shortcode-here]', 'elemetix' ),
		// 		'label_block' => true,
		// 		'render_type' => 'none',
		// 		'classes' => 'elementor_control_message_control-ltr',
		// 		'description' => __( 'Paste shortcode for your success message template.', 'elemetix' ),
		// 	]
		// );

		$element->end_controls_section();
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
			
			$sObjects = array_column($response, 'name');
			foreach ($sObjects as $object) {
				$fieldOptions[$object] = $object;
			}
		}
		return $fieldOptions;
	}
}

new PSEI_Form_Control();