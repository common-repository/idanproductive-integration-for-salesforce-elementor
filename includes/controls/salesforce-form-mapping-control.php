<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
/**
 * Salesforce Form Mapping Control.
 *
 * A control for mapping a Salesforce Table field to the relevant Form Field
 *
 * @since 1.0.0
 */
class PSEI_Form_Mapping_Control extends \Elementor\Base_Data_Control
{


	/**
	 * Get control type.
	 *
	 * Retrieve the control type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Control type.
	 */
	public function get_type()
	{
		return 'salesforce_form_mapping';
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles used by the form control
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue()
	{

	}

	/**
	 * Getcontrol default settings.
	 *
	 * Retrieve the default settings of the control. Used to return
	 * the default settings while initializing the control.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return array Control default settings.
	 */
	protected function get_default_settings()
	{
		return [
			
		];
	}

	/**
	 * Render control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template()
	{
?>

		<# if ( data.index == 0 ) { #>
			<div class="elementor-control-field-description">Map Form Fields to Corresponding Salesforce Fields</div>
		<# } #>
		

		<div class="elementor-control-field ">
			<label for="elementor-control-default-{{data.field_id}}" class="elementor-control-title">{{ data.field_label }}</label>

			<div class="elementor-control-input-wrapper elementor-control-unit-5" style="margin-left: auto;position: relative;width: 50%;">
				<select id="elementor-control-default-{{data.field_id}}" data-setting="salesforce_form_config_{{data.field_name}}">
					<# _.each(data.table_fields, function(value, key) {#>

						<# if (data.form_settings["salesforce_form_config_" + data.field_name] == key) {#>
							<option value="{{key}}" selected>{{key}}</option>
						<# } else {#> 
							<option value="{{key}}">{{key}}</option>
						<# } #>
					<#}); #>
				</select>
			</div>
		</div>


<?php
	}


	/**
	 * Get data control value.
	 *
	 * Retrieve the value of the data control from a specific Controls_Stack settings.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param array $control  Control
	 * @param array $settings Element settings
	 *
	 * @return mixed Control values.
	 */
	public function get_value($control, $settings)
	{

		error_log("===== FORM CONTROL ================================================" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($control) . "\n", 3, ABSPATH . 'error_log');
		error_log("==================================================================" . "\n", 3, ABSPATH . 'error_log');

		error_log("=====WIDGET SETTINGS =============================================================" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($settings) . "\n", 3, ABSPATH . 'error_log');
		error_log("==================================================================" . "\n", 3, ABSPATH . 'error_log');

		if (!isset($control['default'])) {
			$control['default'] = $this->get_default_value();
		}

		if (isset($settings[$control['name']])) {
			$value = $settings[$control['name']];
		} else {
			$value = $control['default'];
		}

		error_log("===== MAPPING VALUE =============================================================" . "\n", 3, ABSPATH . 'error_log');
		error_log(json_encode($value) . "\n", 3, ABSPATH . 'error_log');
		error_log("==================================================================" . "\n", 3, ABSPATH . 'error_log');

		return $value;
	}
}
