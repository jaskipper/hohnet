<?php
/**
 *  Form Manager Class
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_FFM_Emails {


	/**
	 * Get things started
	 */
	public function __construct() {
		add_action( 'give_add_email_tags', array( $this, 'add_ffm_tag' ) );
	}

	/**
	 * Adds a Custom "" Tag
	 * @description: This function creates a custom Give email template tag
	 *
	 * @param $payment_id
	 */
	function add_ffm_tag( $payment_id ) {
		give_add_email_tag( 'all_custom_fields', 'This tag can be used to output a donation form\'s custom field data created through Form Field Manager.', array(
			$this,
			'all_custom_email_tag'
		) );
	}


	/**
	 * Get All Custom Tag
	 *
	 * @description
	 *
	 * @param $payment_id
	 *
	 * @return string|void
	 */
	function all_custom_email_tag( $payment_id ) {

		//Get this form ID from payment
		$form_id = give_get_payment_form_id( $payment_id );

		//Get input field data
		$ffm          = new Give_FFM_Render_Form();
		$form_data    = $ffm->get_input_fields( $form_id );
		$ignore_lists = array( 'section', 'html', 'action_hook', 'file_upload' );
		$output       = '';

		$post_custom = get_post_custom( $payment_id );
		//		echo "<pre>";
		//					var_dump($post_custom);
		//					echo "</pre>";
		//Loop through form fields and match
		foreach ( $form_data as $key => $value ) {


			if ( empty( $value ) ) {
				continue;
			}

			foreach ( $value as $field ) {


				//Double check this input type is set
				if ( ! isset( $field['input_type'] ) ) {
					continue;
				}

				// ignore section break and HTML input type
				if ( in_array( $field['input_type'], $ignore_lists ) ) {
					continue;
				}

				//Whether to return a single value (complex repeaters return array)
				if ( isset( $field['columns'] ) && ! empty( $field['columns'][0] ) ) {
					$field_data = get_post_meta( $payment_id, $field['name'], false );
				} else {
					$field_data = get_post_meta( $payment_id, $field['name'], true );
				}

				//Only show fields with data
				if ( empty( $field_data ) ) {
					continue;
				}

				//Handle repeaters
				$output .= '<p>';
				$output .= '<strong>' . $field['label'] . ':</strong>&nbsp;';

				$repeaters = array( 'repeat', 'multiselect' );

				//Handle various input types' output
				switch ( $field['input_type'] ) {

					case in_array( $field['input_type'], $repeaters ):

						$output .= $this->handle_repeatable_output( $field, $field_data );

						break;

					default :

						$output .= $field_data;

				}

				$output .= '</p>';

			}


		}

		return apply_filters( 'all_custom_email_tag_output', $output );

	}


	/**
	 * @param $field
	 * @param $data
	 *
	 * @return bool|string
	 */
	public function handle_repeatable_output( $field, $data ) {

		//Complex repeater output
		if ( is_array( $data ) ) {

			//Sanity checks
			if ( empty( $data ) || ! is_array( $data ) ) {
				return false;
			}

			$response = '<table><thead><tr>';

			//First output table head
			foreach ( $field['columns'] as $column ) {

				$response .= '<th>' . $column . '</th>';

			}
			$response .= '</tr></thead>';

			//Create table content output
			foreach ( $data as $field ) {

				$field = explode( '| ', $field );

				$response .= '<tr>';

				foreach ( $field as $th ) {

					$response .= '<th>' . $th . '</th>';

				}

				$response .= '</tr>';

			}

			$response .= '</table>';

		} //Simple repeater output
		else {

			$data = explode( '| ', $data );

			//Sanity checks
			if ( empty( $data ) || ! is_array( $data ) ) {
				return false;
			}

			$response = '<ul>';

			foreach ( $data as $field ) {

				$response .= '<li>' . $field . '</li>';

			}

			$response .= '</ul>';

		}


		return apply_filters( 'handle_repeatable_output', $response );


	}

}

new Give_FFM_Emails();